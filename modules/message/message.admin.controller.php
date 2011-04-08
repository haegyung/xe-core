<?php
    /**
     * @class  messageAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief admin controller class of message module
     **/

    class messageAdminController extends message {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Configuration
         **/
        function procMessageAdminInsertConfig() {
            // Get information
            $args->skin = Context::get('skin');
            // Create a module Controller object 
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('message',$args);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_updated');
        }
    }
?>
