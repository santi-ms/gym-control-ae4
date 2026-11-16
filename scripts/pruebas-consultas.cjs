const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const app = fs.readFileSync(path.join(__dirname, '../public/js/app.js'), 'utf8');
const fields = new Map();
function field(id) {
  if (!fields.has(id)) fields.set(id, {
    value: '', textContent: '', hidden: false, error: '', listeners: {},
    setCustomValidity(message) { this.error = message; },
    addEventListener(name, fn) { this.listeners[name] = fn; },
    classList: { toggle() {} },
  });
  return fields.get(id);
}
let send;
let response;
let sessionExpired = false;
const context = vm.createContext({
  $: field,
  alEnviar(id, status, fn) { send = fn; },
  fetch: async () => response(),
  recuperarAcceso() { sessionExpired = true; },
  aviso(id, text) { field(id).textContent = text; },
  moneda: { format: String }, duracion: String,
});
const apiStart = app.indexOf('class ErrorApi');
const apiEnd = app.indexOf('\nfunction elemento', apiStart);
const detailStart = app.indexOf('function actualizarDetalle(');
const detailEnd = app.indexOf('\nfunction marcarOpcion', detailStart);
const counterStart = app.indexOf('function actualizarContadorConsulta(');
const counterEnd = app.indexOf("\nalEnviar('form-plan'", counterStart);
vm.runInContext(`let detalleId = 1; let sesion = {csrf:'test',usuario:{rol:'socio'}}; const borradoresConsulta = new Map();\n${app.slice(apiStart, apiEnd)}\n${app.slice(detailStart, detailEnd)}\n${app.slice(counterStart, counterEnd)}`, context);
const input = field('consulta-mensaje');
const form = {
  reportValidity() { return !input.error; },
  reset() { input.value = ''; },
};
function type(value) { input.value = value; input.listeners.input(); }
function counter() { return field('consulta-contador').textContent; }
function restore(id) { vm.runInContext(`actualizarDetalle({id:${id}, nombre:'Plan', duracion_meses:1, precio:100, categoria:'General', descripcion:'Plan de prueba'})`, context); }
const results = [];
async function test(name, fn) { await fn(); results.push(name); }
(async () => {
  await test('Inicio: contador en cero y minimo de 10 indicado', () => {
    assert.match(counter(), /^0 \/ 500.*Faltan 10/);
  });
  await test('9 caracteres: falta uno y no se envia', async () => {
    type('a'.repeat(9)); assert.match(counter(), /Faltan 1 /);
    let calls = 0; response = () => { calls++; }; await send(form); assert.equal(calls, 0);
  });
  await test('10 y 500 caracteres: validos; 501: invalido', () => {
    for (const length of [10, 500]) { type('a'.repeat(length)); assert.equal(input.error, ''); assert.match(counter(), new RegExp('^'+length+' / 500')); }
    type('a'.repeat(501)); assert.match(input.error, /máximo/);
  });
  await test('Espacios exteriores excluidos y texto original conservado', () => {
    type('  123456789  '); assert.match(counter(), /^9 /); assert.ok(input.error);
    type('  1234567890  '); assert.match(counter(), /^10 /); assert.equal(input.error, '');
  });
  await test('Error de red: conserva texto contador y borrador al volver al plan', async () => {
    const text = 'Consulta pendiente de envio'; type(text);
    response = () => { throw new Error('offline'); };
    await assert.rejects(send(form), /No hay conexión/);
    assert.equal(input.value, text); restore(2); assert.equal(input.value, ''); restore(1);
    assert.equal(input.value, text); assert.match(counter(), /^27 /);
  });
  await test('Error HTTP 500: conserva texto para reintentar', async () => {
    const text = input.value;
    response = () => ({ok:false,status:500,json:async()=>({error:'Error de prueba'})});
    await assert.rejects(send(form), /Error de prueba/); assert.equal(input.value, text);
  });
  await test('Error HTTP 401: activa recuperacion y conserva borrador al regresar', async () => {
    const text = input.value;
    response = () => ({ok:false,status:401,json:async()=>({error:'Sesion vencida'})});
    await assert.rejects(send(form), /Sesion vencida/); assert.equal(sessionExpired, true);
    restore(1); assert.equal(input.value, text);
  });
  await test('Envio correcto: texto vacio contador en cero y borrador eliminado', async () => {
    response = () => ({ok:true,status:201,json:async()=>({id:42,mensaje:'Tu consulta quedó registrada.'})});
    await send(form); assert.equal(input.value, ''); assert.match(counter(), /^0 \/ 500.*Faltan 10/);
    assert.match(field('estado-consulta').textContent, /42/); restore(2); restore(1); assert.equal(input.value, '');
  });
  await test('Texto editado durante el envio: se conserva la nueva version', async () => {
    type('Primera consulta para enviar'); let resolve;
    response = () => new Promise(r => { resolve = r; }); const pending = send(form);
    type('Nuevo texto mientras se envia');
    resolve({ok:true,status:201,json:async()=>({id:43,mensaje:'Registrada'})});
    await pending; assert.equal(input.value, 'Nuevo texto mientras se envia');
  });
  console.log(JSON.stringify({passed:results.length,results}, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
