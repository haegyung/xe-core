<?php
    /**
     * @class  fileModel
     * @author zero (zero@nzeo.com)
     * @brief  file 모듈의 model 클래스
     **/

    class fileModel extends file {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 특정 문서에 속한 첨부파일의 개수를 return
         **/
        function getFilesCount($document_srl) {
            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $output = $oDB->executeQuery('file.getFilesCount', $args);
            return (int)$output->data->count;
        }

        /**
         * @brief 파일 정보를 구함
         **/
        function getFile($file_srl) {
            $oDB = &DB::getInstance();

            $args->file_srl = $file_srl;
            $output = $oDB->executeQuery('file.getFile', $args);
            return $output->data;
        }

        /**
         * @brief 특정 문서에 속한 파일을 모두 return
         **/
        function getFiles($document_srl) {
            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $args->sort_index = 'file_srl';
            $output = $oDB->executeQuery('file.getFiles', $args);

            $file_list = $output->data;

            if($file_list && !is_array($file_list)) $file_list = array($file_list);

            for($i=0;$i<count($file_list);$i++) {
                $direct_download = $file_list[$i]->direct_download;

                if($direct_download!='Y') continue;

                $uploaded_filename = Context::getRequestUri().substr($file_list[$i]->uploaded_filename,2);

                $file_list[$i]->uploaded_filename = $uploaded_filename;
            }
            return $file_list;
        }

        /**
         * @brief 모든 첨부파일을 시간 역순으로 가져옴 (관리자용)
         **/
        function getFileList($obj) {

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // 변수 설정
            $args->sort_index = $obj->sort_index;
            $args->page = $obj->page?$obj->page:1;
            $args->list_count = $obj->list_count?$obj->list_count:20;
            $args->page_count = $obj->page_count?$obj->page_count:10;

            // file.getFileList쿼리 실행
            $output = $oDB->executeQuery('file.getFileList', $args);

            // 결과가 없거나 오류 발생시 그냥 return
            if(!$output->toBool()||!count($output->data)) return $output;

            return $output;
        }

        /**
         * @brief 파일 출력
         * 이미지나 멀티미디어등이 아닌 download를 받을 때 사용하는 method
         **/
        function procDownload($file_srl, $sid) {
            // 파일의 정보를 DB에서 받아옴
            $file_obj = $this->getFile($file_srl);
            if($file_obj->file_srl!=$file_srl||$file_obj->sid!=$sid) exit();

            // 이상이 없으면 download_count 증가
            $args->file_srl = $file_srl;
            $oDB = &DB::getInstance();
            $oDB->executeQuery('file.updateFileDownloadCount', $args);

            // 파일 출력
            $filename = $file_obj->source_filename;

            if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
                $filename = urlencode($filename);
                $filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
            }

            $uploaded_filename = $file_obj->uploaded_filename;
            if(!file_exists($uploaded_filename)) exit();

            $fp = fopen($uploaded_filename, 'rb');
            if(!$fp) exit();

            header("Cache-Control: ");
            header("Pragma: ");
            header("Content-Type: application/octet-stream");

            header("Content-Length: " .(string)($file_obj->file_size));
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header("Content-Transfer-Encoding: binary\n");

            fpassthru($fp);
            exit();
        }

    }
?>
