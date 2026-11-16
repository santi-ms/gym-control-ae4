const { chromium } = require('playwright');
const fs = require('node:fs/promises');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    const page = await browser.newPage();
    const root = path.resolve(__dirname, '..');
    let status = 500;
    let calls = 0;
    await page.route('https://gym-control.test/**', async route => {
      const url = new URL(route.request().url());
      const plan = {id:1,nombre:'Plan Mensual',duracion_meses:1,precio:25000,categoria:'General',descripcion:'Plan de prueba'};
      if (url.pathname.startsWith('/api/')) {
        let body;
        let code = 200;
        if (url.pathname.endsWith('login.php')) body = {usuario:{id:1,nombre:'Socio de prueba',email:'socio@example.test',rol:'socio'},csrf:'test'};
        else if (url.pathname.endsWith('planes.php')) body = url.searchParams.has('id') ? {plan} : {planes:[plan]};
        else if (url.pathname.endsWith('consultas.php')) {
          calls++;
          code = status;
          body = code === 201 ? {id:42,mensaje:'Tu consulta quedó registrada.'} : {error:'Error de prueba: reintentá el envío.'};
        } else throw new Error('API inesperada: ' + url);
        await route.fulfill({status:code,contentType:'application/json',body:JSON.stringify(body)});
        return;
      }
      const relative = url.pathname === '/public/' ? 'public/index.html' : url.pathname.slice(1);
      const contentType = relative.endsWith('.js') ? 'application/javascript' : relative.endsWith('.css') ? 'text/css' : 'text/html';
      await route.fulfill({status:200,contentType,body:await fs.readFile(path.join(root, relative))});
    });
    await page.goto('https://gym-control.test/public/#detalle/1');
    const input = page.locator('#consulta-mensaje');
    const count = page.locator('#consulta-contador');
    const submit = page.locator('#form-consulta button[type=submit]');
    await input.waitFor({state:'visible'});
    const results = [];
    async function check(name, fn) { await fn(); results.push(name); }
    await check('Inicio en 0 / 500', async () => assert.match(await count.innerText(), /^0 \/ 500/));
    await check('9 caracteres bloquean el envio nativo', async () => {
      await input.fill('a'.repeat(9)); await submit.click(); assert.equal(calls, 0);
      assert.equal(await input.evaluate(el => el.checkValidity()), false);
    });
    await check('10 caracteres cumplen el minimo', async () => {
      await input.fill('a'.repeat(10)); assert.equal(await input.evaluate(el => el.checkValidity()), true);
    });
    await check('500 caracteres aceptados', async () => {
      await input.fill('a'.repeat(500)); assert.match(await count.innerText(), /^500 /);
      assert.equal(await input.evaluate(el => el.checkValidity()), true);
    });
    await check('501 caracteres rechazados', async () => {
      await input.fill('a'.repeat(501)); await submit.click(); assert.equal(calls, 0);
      assert.equal(await input.evaluate(el => el.checkValidity()), false);
    });
    await check('500 emojis aceptados por campo y contador', async () => {
      await input.fill('😀'.repeat(500)); assert.equal(Array.from(await input.inputValue()).length, 500);
      assert.match(await count.innerText(), /^500 /); assert.equal(await input.evaluate(el => el.checkValidity()), true);
    });
    await check('Espacios exteriores no consumen el limite util', async () => {
      await input.fill('  ' + 'a'.repeat(500) + '  ');
      assert.equal((await input.inputValue()).length, 504); assert.match(await count.innerText(), /^500 /);
      assert.equal(await input.evaluate(el => el.checkValidity()), true);
    });
    await check('HTTP 500 conserva texto y contador', async () => {
      await input.fill('Consulta de prueba que debe conservarse'); await submit.click();
      await page.locator('#estado-consulta').filter({hasText:'Error de prueba'}).waitFor();
      assert.equal(await input.inputValue(), 'Consulta de prueba que debe conservarse');
      assert.match(await count.innerText(), /^39 /);
    });
    await check('HTTP 201 limpia el campo y reinicia el contador', async () => {
      status = 201; await submit.click();
      await page.locator('#estado-consulta').filter({hasText:'42'}).waitFor();
      assert.equal(await input.inputValue(), ''); assert.match(await count.innerText(), /^0 \/ 500/);
    });
    console.log(JSON.stringify({passed:results.length, results, entorno:'Chrome real; API simulada; sin PHP/MySQL'}, null, 2));
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
