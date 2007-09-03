<?php
    /**
     * @archivo   es.lang.php
     * @autor zero (zero@nzeo.com)
     * @sumario Paquete del idioma español para importar.
     **/

    // Palabras para los botones
    $lang->cmd_sync_member = 'Sincronizar';
    $lang->cmd_continue = 'Continuar';

    // Especificaciones
    $lang->importer = 'Transferir los datos de zeroboard';
    $lang->source_type = 'Objetivo a transferir';
    $lang->type_member = 'Datos del usuario';
    $lang->type_module = 'Datos de los artículos.';
    $lang->type_syncmember = 'Sincronizar datos del usuario';
    $lang->target_module = 'Objetivo del módulo';
    $lang->xml_file = 'Archivo XML';

    $lang->import_step_title = array(
        1 => 'Paso 1. Seleccione el objetivo a transferir',
        12 => 'Paso 1-2. Seleccione el objetivo del módulo ',
        13 => 'Paso 1-3. Seleccione la categoría del módulo',
        2 => 'Paso 2. Subir el archivo XML',
        3 => 'Paso 2. Sincronizar los datos del usuario y de los artículos',
    );

    $lang->import_step_desc = array(
        1 => 'Por favor seleccione el tipo de archivo XML a transfrerir.',
        12 => 'Por favor seleccione el módulo para transferir los datos.',
        13 => 'Por favor seleccione la categoría para transferir los datos.',
        2 => "Por favor ingrese la ubicación del archivo XML para transfer los datos.\nPuede ser ruta absoluto o relativo.",
        3 => 'La información del usuario y del artículo podría ser incorrecto luego de la transferencia. Si ese es el caso, sincroniza para la corrección basado a la ID del usuario.',
    );

    // Guía/ Alerta
    $lang->msg_sync_member = 'Al presionar el botón sincronizar comenzará a sincronizar los datos usuario y el artículo.';
    $lang->msg_no_xml_file = 'No se puede encontrar el archivo XML. Verifique su ruta.';
    $lang->msg_invalid_xml_file = 'Tipo de archivo XML inválido.';
    $lang->msg_importing = 'Ingresando %d dotos de %d. (Si esto mantiene paralizado presione el botón "Continuar".)';
    $lang->msg_import_finished = '%d datos fueron completamente ingresados. Dependiendo del caso, pueden haber algunos datos no ingresados.';
    $lang->msg_sync_completed = 'Sincronización del usuario, artículo y respuestas finalizadas.';

    // bla bla...
    $lang->about_type_member = 'Seleccione esta opción si estas transferiendo la información del usuario.';
    $lang->about_type_module = 'Seleccione esta opción si estas transfeririendo información de articulos de los tableros';
    $lang->about_type_syncmember = 'Seleccione esta opción cuando tenga que sincronizar la información del usuario luego de haber transferodo la información del usuario y del artículo.';
    $lang->about_importer = "Es posible trasferir los datos de Zeroboard4, zb5beta o de otros programas a ZeroBoardXE.\nPara la transferencia debe utilizar <a href=\"#\" onclick=\"winopen('');return false;\">Exportador XML</a> para transformar los datos en archivo XML, y luego subir ese archivo.";

    $lang->about_target_path = "Para bajar los archivos adjuntos de ZeroBoard4, ingresa la ubicación de ZeroBoard4 instalado.\nSi esta en el mismo servidor escribe la ubicación de ZeroBoard4 como por ejemplo: /home/ID/public_html/bbs o si esta en otro servidor escribe la ubicación de ZeroBoard4 instalado como por ejemplo: http://dominio/bbs";
?>
