<?php
    /**
     * @class  commentAdminView
     * @author NHN (developers@xpressengine.com)
     * @brief admin view class of the comment module
     **/

    class commentAdminView extends comment {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Display the list(for administrators)
         **/
        function dispCommentAdminList() {
            // option to get a list
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 30; // / the number of postings to appear on a single page
            $args->page_count = 10; // / the number of pages to appear on the page navigation

            $args->sort_index = 'list_order'; // /< Sorting values

            $args->module_srl = Context::get('module_srl');

            // get a list by using comment->getCommentList. 
            $oCommentModel = &getModel('comment');
            $output = $oCommentModel->getTotalCommentList($args);
            // set values in the return object of comment_model:: getTotalCommentList() in order to use a template.
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('comment_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
            // set the template 
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('comment_list');
        }

        /**
         * @brief show the blacklist of comments in the admin page
         **/
        function dispCommentAdminDeclared() {
            // option to get a blacklist
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 30; // /< the number of comment postings to appear on a single page
            $args->page_count = 10; // /< the number of pages to appear on the page navigation

            $args->sort_index = 'comment_declared.declared_count'; // /< sorting values
            $args->order_type = 'desc'; // /< sorted value

            // get a list
            $declared_output = executeQuery('comment.getDeclaredList', $args);

            if($declared_output->data && count($declared_output->data)) {
                $comment_list = array();

                $oCommentModel = &getModel('comment');
                foreach($declared_output->data as $key => $comment) {
                    $comment_list[$key] = new commentItem();
                    $comment_list[$key]->setAttribute($comment);
                }
                $declared_output->data = $comment_list;
            }
        
            // set values in the return object of comment_model:: getCommentList() in order to use a template.
            Context::set('total_count', $declared_output->total_count);
            Context::set('total_page', $declared_output->total_page);
            Context::set('page', $declared_output->page);
            Context::set('comment_list', $declared_output->data);
            Context::set('page_navigation', $declared_output->page_navigation);
            // set the template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('declared_list');
        }
    }
?>
