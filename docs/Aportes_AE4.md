# Aportes del equipo · Gym Control AE4

## Santiago Tarnoski · aportes registrados

| Aporte | Evidencia en Git | Aplicación en la AE4 |
| --- | --- | --- |
| Catálogo con búsqueda por nombre, descripción y categoría y filtro por duración. | [Commit de Santiago: buscador de planes y tarjeta 3](https://github.com/santiagotarnoski/gym-control-ae4/commit/874c841a849122a6bc71e158d58e39fd188e2193) | La SPA conserva la búsqueda sin tildes y el filtro combinado. `normalizar()` y `pintarTarjetas()` procesan los planes recibidos de `GET api/planes.php`. |
| Validación del cupón `UCP10` en la selección del plan. | [Commit de Santiago: validación de cupón](https://github.com/santiagotarnoski/gym-control-ae4/commit/1302629a602ef1b7259431b6ce899032c3bec384) | La SPA conserva la validación visual. La AE4 amplía el flujo al calcular y guardar el descuento en el servidor mediante `api/membresias.php` y `MembresiaModel.php`. |

Los enlaces identifican los aportes originales de Santiago. En esta copia se registran en el [commit de importación del buscador y cupón](https://github.com/santi-ms/gym-control-ae4/commit/5a21fde). La búsqueda normalizada y el filtro combinado se conservan en la SPA.

## Franco Olexyn · aporte implementado y probado localmente

**Recuperar el acceso cuando vence la sesión durante una operación.**

1. En [`public/js/app.js`](../public/js/app.js), las operaciones protegidas identifican sus llamadas con `requiereSesion`. Ante un 401, la interfaz limpia la sesión, oculta los controles protegidos y muestra Ingresar con un aviso.
2. Si el socio estaba eligiendo una membresía, se conserva el plan realmente seleccionado para regresar después de ingresar. Una clave incorrecta mantiene su aviso en el formulario de ingreso; la consulta anónima de sesión permite seguir navegando por las vistas públicas.
3. Se verificaron el cierre de sesión desde otra pestaña, la clave incorrecta, el retorno al plan y la confirmación real de la membresía con PHP y MariaDB. Los pasos, resultados y límites del entorno están en [`pruebas-franco-sesion.md`](pruebas-franco-sesion.md).

[Commit del aporte de Franco](https://github.com/santi-ms/gym-control-ae4/commit/702db03).

## Santino Martins · contador implementado

**Contador de caracteres en Reclamos y consultas.**

1. El formulario muestra un contador actualizado al escribir, el mínimo de 10 y el máximo de 500 caracteres, con ayuda vinculada al campo y anuncios mediante `aria-live`.
2. Los mensajes con menos de 10 caracteres útiles no se envían. Ante un error, se conserva el texto por plan durante la navegación. Al confirmarse el envío, se reinician el campo y el contador sin borrar una nueva edición pendiente.
3. Los casos verificados y los límites del entorno están en [`pruebas-santino-consultas.md`](pruebas-santino-consultas.md). El servidor mantiene su validación de longitud antes de guardar.

[Commit del contador de Santino](https://github.com/santi-ms/gym-control-ae4/commit/9ca9adb30912d33987c4ced227766b7ec741635a).

## Procedencia y documentación individual

El repositorio de entrega es [santi-ms/gym-control-ae4](https://github.com/santi-ms/gym-control-ae4). Parte del trabajo grupal original y registra su importación por componentes y los aportes posteriores. Los demás commits de preparación de esta copia se atribuyen a `santi-ms`; esa atribución identifica la importación y conserva los créditos del equipo.

Los [informes individuales](informes-individuales/README.md) se conservan como antecedentes de sus respectivas actividades. Los registros de pruebas de Franco y Santino describen por separado el alcance y los límites de sus verificaciones.
