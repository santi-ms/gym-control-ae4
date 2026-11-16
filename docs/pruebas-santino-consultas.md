# Contador de Reclamos y consultas · Santino Martins

El formulario muestra el mínimo de 10, el máximo de 500 y el contador actualizado al escribir. Los espacios al principio y al final no cuentan para el mínimo. La ayuda está vinculada al campo y el contador se anuncia mediante una región `aria-live`.

El borrador se conserva por plan durante la navegación de la SPA, incluso si se debe recuperar el acceso después de vencer la sesión. Un error de envío conserva el mensaje. Una respuesta correcta vacía el campo, elimina el borrador enviado y devuelve el contador a cero. Si se escribe otro mensaje mientras llega la respuesta, ese texto nuevo se conserva. Los borradores viven en memoria y no sobreviven a una recarga de página.

## Verificación realizada

Se comprobó la sintaxis de `public/js/app.js` con `node --check`. Se ejecutaron nueve pruebas de comportamiento en Node con controles y respuestas HTTP simulados, utilizando las funciones reales del formulario y de acceso a la API:

Para repetirlas desde la raíz del proyecto: `node scripts/pruebas-consultas.cjs`.

| Caso | Resultado |
| --- | --- |
| Inicio | 0 / 500 y faltan 10. |
| 9 caracteres | Falta uno y no se envía. |
| 10, 500 y 501 caracteres | Los dos primeros son válidos; 501 se rechaza. |
| Espacios exteriores | Se excluyen del mínimo sin alterar el texto escrito. |
| Error de red | Se conservan texto, contador y borrador al regresar al plan. |
| HTTP 500 | Se conserva el mensaje para reintentar. |
| HTTP 401 | Se activa la recuperación del acceso y se conserva el borrador. |
| HTTP 201 | Se vacían campo y borrador y se reinicia el contador. |
| Edición durante el envío | La respuesta anterior no borra el nuevo texto. |

La validación del servidor existente permanece en `api/consultas.php`: `texto($datos, 'mensaje', 10, 500)`. No se ejecutaron PHP, MySQL ni pruebas con XAMPP en este entorno. La verificación de persistencia real y del anuncio con lector de pantalla queda para la instalación local.
