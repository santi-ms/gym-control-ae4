# Contador de Reclamos y consultas · Santino Martins

El formulario muestra el mínimo de 10, el máximo de 500 y el contador actualizado al escribir. Los espacios al principio y al final no cuentan para esos límites. Se cuentan puntos de código Unicode: un emoji simple cuenta como uno, aunque un símbolo compuesto puede contener varios. La ayuda está vinculada al campo y el contador se anuncia mediante una región `aria-live`.

El borrador se conserva por plan durante la navegación de la SPA, incluso si se debe recuperar el acceso después de vencer la sesión. Un error de envío conserva el mensaje. Una respuesta correcta vacía el campo, elimina el borrador enviado y devuelve el contador a cero. Si se escribe otro mensaje mientras llega la respuesta, ese texto nuevo se conserva. Los borradores viven en memoria y no sobreviven a una recarga de página.

## Verificación realizada

Fecha: 16/11/2026. Se comprobó la sintaxis de `public/js/app.js` con `node --check`. Se ejecutaron once pruebas de comportamiento en Node con controles y respuestas HTTP simulados, utilizando las funciones reales del formulario y de acceso a la API:

Para repetirlas desde la raíz del proyecto: `node scripts/pruebas-consultas.cjs`.

| Caso | Resultado |
| --- | --- |
| Inicio | 0 / 500 y faltan 10. |
| 9 caracteres | Falta uno y no se envía. |
| 10, 500 y 501 caracteres | Los dos primeros son válidos; 501 se rechaza. |
| Espacios exteriores | Se excluyen del mínimo sin alterar el texto escrito. |
| 500 y 501 emojis simples | Se aceptan 500 y se rechazan 501. |
| 500 caracteres con espacios exteriores | El contador y la validación mantienen el límite de 500 útiles. |
| Error de red | Se conservan texto, contador y borrador al regresar al plan. |
| HTTP 500 | Se conserva el mensaje para reintentar. |
| HTTP 401 | Se activa la recuperación del acceso y se conserva el borrador. |
| HTTP 201 | Se vacían campo y borrador y se reinicia el contador. |
| Edición durante el envío | La respuesta anterior no borra el nuevo texto. |

## Verificación en navegador

También pasaron nueve casos en Google Chrome real con la interfaz completa y respuestas HTTP simuladas: inicio en cero; rechazo de 9 caracteres; aceptación de 10 y 500; rechazo de 501; aceptación de 500 emojis simples; espacios exteriores; conservación ante HTTP 500; limpieza y reinicio ante HTTP 201. Se retiró el límite nativo basado en unidades UTF-16 para que la validación JavaScript y el contador utilicen el mismo criterio.

Para repetir esta prueba, se necesita Google Chrome y el paquete de desarrollo `playwright`. Desde la raíz del proyecto:

```powershell
npm install --no-save --package-lock=false playwright
node scripts/pruebas-consultas-navegador.cjs
```

La prueba sirve los archivos mediante interceptación local del navegador y simula la API. No inicia un servidor PHP ni escribe en una base de datos.

La validación del servidor existente permanece en `api/consultas.php`: `texto($datos, 'mensaje', 10, 500)`. No se ejecutaron PHP, MySQL ni pruebas con XAMPP en esta verificación del contador. La persistencia real y el anuncio con lector de pantalla quedan para la instalación local. Los resultados históricos de Lighthouse no se presentan como una nueva auditoría de este cambio.
