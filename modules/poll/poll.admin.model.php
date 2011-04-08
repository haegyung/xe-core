<?php
    /**
     * @class  pollAdminModel
     * @author NHN (developers@xpressengine.com)
     * @brief The admin model class of the poll module
     **/

    class pollAdminModel extends poll {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Get the list of polls
         **/
        function getPollList($args) {
            $output = executeQuery('poll.getPollList', $args);
            if(!$output->toBool()) return $output;

            if($output->data && !is_array($output->data)) $output->data = array($output->data);
            return $output;
        }

        /**
         * @brief Get the original poll
         **/
        function getPollAdminTarget() {
            $poll_srl = Context::get('poll_srl');
            $upload_target_srl = Context::get('upload_target_srl');

            $oDocumentModel = &getModel('document');
            $oCommentModel = &getModel('comment');

            $oDocument = $oDocumentModel->getDocument($upload_target_srl);

            if(!$oDocument->isExists()) $oComment = $oCommentModel->getComment($upload_target_srl);

            if($oComment && $oComment->isExists()) {
                $this->add('document_srl', $oComment->get('document_srl'));
                $this->add('comment_srl', $oComment->get('comment_srl'));
            } elseif($oDocument->isExists()) {
                $this->add('document_srl', $oDocument->get('document_srl'));
            } else return new Object(-1, 'msg_not_founded');
        }

    }
?>
