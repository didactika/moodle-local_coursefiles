<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * User-facing language strings.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'CourseBridge';
$string['settings'] = 'Políticas de materiales';
$string['profiles'] = 'Perfiles de relación';
$string['dashboard'] = 'Materiales del curso';
$string['profilejson'] = 'Configuración del perfil (JSON)';
$string['profile'] = 'Perfil';
$string['saveprofile'] = 'Validar y activar revisión';
$string['preview'] = 'Previsualizar migración';
$string['sourceedition'] = 'Edición de origen';
$string['targetedition'] = 'Edición de destino';
$string['layoutconfirmed'] = 'He revisado la estructura de las secciones de origen y destino';
$string['execute'] = 'Ejecutar el plan revisado';
$string['cancelrun'] = 'Cancelar ejecución';
$string['publish'] = 'Publicar módulo';
$string['revoke'] = 'Revocar publicación';
$string['cmid'] = 'ID del módulo del curso';
$string['coursepolicy'] = 'Política de materiales del curso';
$string['author_policy'] = 'Política de autoría';
$string['author_policy_desc'] = 'El bloqueo impide copiar a destinos donde no se haya demostrado la protección antes de guardar. El modo editable conserva los metadatos iniciales y la procedencia, pero permite ediciones nativas posteriores.';
$string['preserve_locked'] = 'Conservar y bloquear el autor declarado';
$string['editable_with_provenance'] = 'Permitir cambios conservando la procedencia';
$string['author_course_override'] = 'Configuración por curso';
$string['author_course_override_desc'] = 'Los cursos pueden heredar o endurecer la política del sitio, nunca debilitarla.';
$string['disabled'] = 'Desactivada';
$string['tighten_only'] = 'Permitir una política más estricta';
$string['inherit'] = 'Heredar la política del sitio';
$string['author_missing_policy'] = 'Autor no declarado';
$string['author_missing_policy_desc'] = 'Un material sin autor declarado nunca se atribuye automáticamente al docente que lo importa.';
$string['preserve_unknown'] = 'Conservar el autor sin declarar';
$string['block_reuse'] = 'Bloquear hasta corregir la declaración';
$string['maintenance_required'] = 'La migración requiere modo de mantenimiento y una ventana exclusiva de edición administrativa.';
$string['lockedunsupported'] = 'Este destino no puede impedir cambios nativos posteriores del autor. No se permite copiar materiales bloqueados. Consulta la documentación de cobertura de autoría.';
$string['invalidprofile'] = 'Configuración inválida o no admitida. Revisa el esquema JSON, las capturas y las opciones compatibles.';
$string['ambiguousprofile'] = 'Varios patrones generan identidades de curso contradictorias.';
$string['regexfailed'] = 'La expresión regular es inválida o supera el límite de trabajo.';
$string['invalidpolicy'] = 'La política del curso no está permitida por el sitio.';
$string['missingauthor'] = 'El material no tiene autor declarado y la política impide reutilizarlo.';
$string['materialunavailable'] = 'El material no está disponible o queda fuera de tus permisos de reutilización.';
$string['placementambiguous'] = 'La sección o carpeta de destino falta, es ambigua o no es compatible.';
$string['layoutrequired'] = 'Selecciona ediciones distintas no vacías y confirma la estructura si se emparejan secciones por posición.';
$string['planstale'] = 'El plan revisado ya no coincide con el estado de cursos, archivos, autoría o políticas.';
$string['verificationfailed'] = 'El archivo guardado no coincide con el material revisado.';
$string['lockfailed'] = 'El destino o catálogo está ocupado. Inténtalo más tarde.';
$string['invalidcursor'] = 'Cursor de catálogo inválido.';
$string['targetrequired'] = 'Abre el repositorio desde un formulario de edición de curso.';
$string['noprofile'] = 'No existe un perfil activo. Solicita su configuración a un administrador.';
$string['reconcilecatalog'] = 'Actualizar el catálogo de materiales';
$string['notice'] = 'Versión de desarrollo. El bloqueo requiere una integración de destino verificada; las rutas nativas solo están disponibles con políticas de autoría editable y procedencia.';
$string['privacy_metadata'] = 'CourseBridge stores rule revisions, publication authority, declared attribution, copy lineage, migration attempts and scoped log history.';
$string['privacy_userid'] = 'The user who configured, published, imported or executed the action.';
$string['privacy_details'] = 'Configuration, declared attribution and structured action outcomes.';
$string['reindex'] = 'Actualizar catálogo del curso';
$string['log'] = 'Logoría reciente';
$string['state'] = 'Estado';
$string['decision'] = 'Decisión';
$string['source'] = 'Origen';
$string['destination'] = 'Destino';
$string['report'] = 'Informe de migración';
$string['materials'] = 'Materiales';
$string['nextpage'] = 'Página siguiente';
$string['none'] = 'No hay materiales permitidos que coincidan.';
$string['revisionactivated'] = 'Revisión activada. Actualiza el catálogo o espera la tarea programada.';
$string['policyupdated'] = 'Política del curso guardada.';
$string['published'] = 'Publicación actualizada.';
$string['coursebridge:browse'] = 'Browse related materials';
$string['coursebridge:reuse'] = 'Reuse related materials';
$string['coursebridge:publish'] = 'Publish historical materials';
$string['coursebridge:manageprofiles'] = 'Manage relationship profiles';
$string['coursebridge:planmigration'] = 'Preview bulk migrations';
$string['coursebridge:executemigration'] = 'Execute and cancel reviewed migrations';
$string['coursebridge:viewlog'] = 'Read CourseBridge log';
$string['coursebridge:managecoursepolicy'] = 'Manage inherited course material policy';
$string['coursebridge:correctattribution'] = 'Correct attribution through an authorized integration';
$string['catalogintro'] = 'Encuentra archivos docentes de cursos relacionados. Utiliza los filtros para acotar los resultados.';
$string['originhelp'] = 'Cada archivo aparece una sola vez. Las filas identifican los cursos, las ediciones y los autores originales disponibles.';
$string['emptyhelp'] = 'Prueba otra edición o elimina filtros. Solo se muestran materiales a los que tienes acceso.';
$string['clearfilters'] = 'Limpiar filtros';
$string['pagination'] = 'Páginas de materiales';
$string['filtermaterials'] = 'Filtrar materiales';
$string['filenamefilter'] = 'Nombre del archivo';
$string['editionfilter'] = 'Edición';
$string['modulefilter'] = 'Actividad de origen';
$string['filetypefilter'] = 'Tipo de archivo';
$string['allmodules'] = 'Todas las actividades compatibles';
$string['alltypes'] = 'Todos los tipos de archivo';
$string['plaintext'] = 'Texto sin formato';
$string['origincount'] = '{$a} orígenes disponibles';
$string['originscaption'] = 'Orígenes disponibles de {$a}';
$string['noedition'] = 'Sin edición';
$string['unknownauthor'] = 'Autor sin declarar';
$string['catalogmaintenance'] = 'Actualizar materiales disponibles';
$string['refreshhelp'] = 'Solicita una actualización cuando cambien los materiales del curso. Puedes seguir consultando el catálogo.';
$string['refreshqueued'] = 'Actualización del catálogo solicitada.';
$string['publicationtitle'] = 'Compartir materiales con cursos relacionados';
$string['publicationhelp'] = 'Elige una carpeta o un recurso de archivo para permitir su reutilización en cursos relacionados. Retirar la publicación impide nuevas reutilizaciones; las copias existentes permanecen.';
$string['publicationmodule'] = 'Carpeta o recurso de archivo';
$string['nopublicationmodules'] = 'Este curso no tiene carpetas ni recursos de archivo compatibles.';
$string['publishedlabel'] = 'Publicado';
$string['policyintro'] = 'El curso hereda la política de autor del sitio. Puede aplicar una política más estricta cuando el sitio lo permite.';
$string['effectivepolicy'] = 'Política efectiva';
$string['lockedpolicyhelp'] = 'La copia está bloqueada porque los editores nativos de Moodle permiten cambiar el autor posteriormente.';
$string['editablepolicyhelp'] = 'Las copias mantienen el autor y la licencia originales. Se permiten cambios posteriores desde los editores de Moodle.';
$string['policymanagedbysite'] = 'El administrador del sitio gestiona esta política.';
$string['backtomaterials'] = 'Volver a los materiales';
$string['migrationintro'] = 'Elige las ediciones de origen y destino. Revisa la lista de archivos resultante antes de copiar en las carpetas existentes.';
$string['planreference'] = 'Referencia del plan';
$string['reviewissues'] = 'Problemas que resolver antes de copiar';
$string['reviewbeforeexecute'] = 'Comprueba las carpetas de destino y las decisiones siguientes. Las copias requieren el modo de mantenimiento del sitio y una ventana exclusiva de edición administrativa.';
$string['newpreview'] = 'Crear otra previsualización';
$string['runreference'] = 'Ejecución {$a}';
$string['migrationtablecaption'] = 'Archivos revisados y resultados actuales';
$string['nomigrationoperations'] = 'Ningún archivo coincide con las ediciones y las reglas de ubicación seleccionadas.';
$string['metric_copy'] = 'Archivos que copiar';
$string['metric_skip'] = 'Ya disponibles';
$string['metric_blocked'] = 'Archivos bloqueados';
$string['progresslabel'] = '{$a->done} de {$a->total} decisiones procesadas';
$string['runprocessed'] = '{$a} decisiones procesadas en esta ejecución';
$string['planprogress'] = 'Progreso total del plan';
$string['sectionnumber'] = 'Sección {$a}';
$string['profilesintro'] = 'Los perfiles definen qué cursos están relacionados y cómo se emparejan los materiales.';
$string['revision'] = 'Revisión activa';
$string['newprofile'] = 'Nuevo perfil';
$string['profileeditor'] = 'Configuración del perfil';
$string['operationfailed'] = 'No se ha podido completar la operación. Revisa los permisos del curso y de los materiales.';
$string['ambiguous_course_pair'] = 'No existe una pareja única de cursos de origen y destino para las ediciones seleccionadas.';
$string['outcome_reviewable'] = 'Listo para revisar';
$string['outcome_blocked'] = 'Bloqueado';
$string['outcome_copy'] = 'Copiar';
$string['outcome_skip_identical'] = 'Omitir: archivo idéntico';
$string['outcome_skip_content'] = 'Omitir: contenido ya disponible';
$string['outcome_blocked_content'] = 'Bloqueado: contenido diferente en esta ruta';
$string['outcome_blocked_attribution'] = 'Bloqueado: autor o licencia en conflicto';
$string['outcome_blocked_policy'] = 'Bloqueado por la política de autor';
$string['outcome_pending'] = 'Pendiente';
$string['outcome_done'] = 'Procesado';
$string['outcome_queued'] = 'En cola';
$string['outcome_running'] = 'En curso';
$string['outcome_completed'] = 'Completado';
$string['outcome_partial'] = 'Finalizado con archivos bloqueados';
$string['outcome_cancelled'] = 'Cancelado';
$string['outcome_copied'] = 'Copiado';
$string['outcome_already_present'] = 'Ya disponible';
$string['outcome_enabled'] = 'Activado';
$string['outcome_disabled'] = 'Desactivado';
$string['logintro'] = 'Acciones realizadas en CourseBridge. Usa la referencia de una migración o perfil para consultar sus registros.';
$string['logaction'] = 'Acción';
$string['logallactions'] = 'Todas las acciones';
$string['logreference'] = 'Referencia';
$string['logunknownuser'] = 'Sistema o usuario eliminado';
$string['logempty'] = 'No hay registros con estos filtros.';
$string['logolder'] = 'Registros anteriores';
$string['logaction_profile_activated'] = 'Perfil activado';
$string['logaction_course_policy_changed'] = 'Política de autor del curso modificada';
$string['logaction_publication_granted'] = 'Materiales compartidos';
$string['logaction_publication_revoked'] = 'Materiales sin compartir';
$string['logaction_migration_previewed'] = 'Vista previa de migración creada';
$string['logaction_migration_queued'] = 'Migración en cola';
$string['logaction_migration_blocked'] = 'Migración bloqueada';
$string['logaction_migration_batch'] = 'Lote de migración procesado';
