# Evidencias de la AE4

## Fechas y alcance

| Conjunto | Fecha | Alcance |
| --- | --- | --- |
| Lighthouse | 15/11/2026 | Accesibilidad de nueve vistas de la versión auditada. |
| Registros generales de API e interfaz | Línea base documentada en el informe del 15/11/2026 | 40 comprobaciones HTTP y 10 de interfaz; los JSON no incluyen fechas individuales. |
| [Recuperación de sesión de Franco](../pruebas-franco-sesion.md) | 16/11/2026 | Prueba registrada con PHP y MariaDB reales; no XAMPP/Apache. |
| [Contador de Santino](../pruebas-santino-consultas.md) | 16/11/2026 | Pruebas de comportamiento con controles y respuestas HTTP simulados. |

Los reportes del 15/11 son anteriores a los cambios de sesión y contador. El 100/100 histórico no representa una nueva auditoría de esos cambios. La persistencia y accesibilidad de la versión final requieren comprobación en la instalación de entrega.

## Evidencias de la línea base

- [Resumen de Lighthouse](lighthouse-resultados.json)
- [Captura de accesibilidad de la vista Seleccionar plan](lighthouse-accesibilidad.png)
- [Pruebas de la API HTTP](pruebas-api.json)
- [Pruebas de navegación y teclado](pruebas-interfaz.json)

Reportes de Lighthouse en HTML. Descargalos y abrilos en Chrome para consultar los resultados completos.

| Vista | Reporte |
| --- | --- |
| Portada | [Inicio](lighthouse-inicio.html) |
| Comparación | [Tabla](lighthouse-tabla.html) |
| Catálogo | [Tarjetas](lighthouse-tarjetas.html) |
| Ficha como visitante | [Detalle](lighthouse-detalle.html) |
| Ingreso y registro | [Ingreso](lighthouse-ingreso.html) |
| Confirmación de membresía | [Selección](lighthouse-seleccion.html) |
| Portal del socio | [Cuenta](lighthouse-socio.html) |
| Ficha como socio | [Detalle autenticado](lighthouse-detalle-socio.html) |
| Gestión de planes | [Administración](lighthouse-administracion.html) |

Los reportes de la línea base usan una instalación local de XAMPP con Apache, PHP 8.2.12 y el motor MariaDB 10.4.32 de XAMPP. La auditoría automatizada se complementó con pruebas de navegación mediante teclado. Los tiempos HTTP corresponden a localhost y dependen del equipo. Los entornos de los aportes posteriores se describen por separado.
