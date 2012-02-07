<?php
    /**
     * @class  counterAdminView
     * @author NHN (developers@xpressengine.com)
     * @brief Admin view class of counter module
     **/

    class counterAdminView extends counter {

        /**
         * @brief Initialization
         **/
        function init() {
            // set the template path
            $this->setTemplatePath($this->module_path.'tpl');
        }

        /**
         * @brief Admin page 
         **/
        function dispCounterAdminIndex() {
            // set today's if no date is given
            $selected_date = Context::get('selected_date');
            if(!$selected_date) $selected_date = date("Ymd");
            Context::set('selected_date', $selected_date);
            // create the counter model object
            $oCounterModel = getModel('counter');
            // get a total count and daily count
            $site_module_info = Context::get('site_module_info');
            $status = $oCounterModel->getStatus(array(0,$selected_date),$site_module_info->site_srl);
            Context::set('total_counter', $status[0]);
            Context::set('selected_day_counter', $status[$selected_date]);
            // get data by time, day, month, and year
            $type = Context::get('type');
            if(!$type) {
                $type = 'day';
                Context::set('type',$type);
            }
            $detail_status = $oCounterModel->getHourlyStatus($type, $selected_date, $site_module_info->site_srl);
            Context::set('detail_status', $detail_status);
            
            // display
            $this->setTemplateFile('index');
        }

    }
?>
