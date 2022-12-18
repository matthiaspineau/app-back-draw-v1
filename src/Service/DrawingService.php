<?php

class DrawingService {

    public function __construct() {
		
	}

    public function deleteDraw($params) {

        $result = array();

        $drawingDao = new DrawingDao();
        $result = $drawingDao->deleteDraw($params);

		if ($result['response'] !== '200') {
            return $result;
		} else {
			$filename = $params['name'];

            $arrayFormatName = array('original', 'large', 'medium', 'small', 'thumbnail');
            
            foreach($arrayFormatName as $value) {
                $pathUploads = dirname(__FILE__, 4) . '/uploads/' . $value;
                $ext = '.png';
                $location =  $pathUploads.'/'.$filename.$ext;
                unlink($location);
            }
           
		}

        return $result;
    }

    public function getDraw($params) {

        $result = array();

        $drawingDao = new DrawingDao();
        $result = $drawingDao->getDrawing($params);

        return $result;
    }

    /**
     * upload image : 
     *  - enregistre en bdd
     *  - upload image au format different
     *  - upload image original
     */
    public function uploadImage($params) {
        
        $result = array();
        if (!isset($_FILES['file'])) {
            $result['response'] == 400;
        } 

        $timestamp = time();
        $params['time'] = $timestamp; 

        $file = $_FILES;
        $file['file']['new_name'] = $timestamp . '_' . $file['file']['name']; 
        // var_dump( $file);
        // var_dump($params);
        // ----- save in bdd
        $drawingDao = new DrawingDao();
        $result = $drawingDao->saveDrawing($params);

        

        // ------ upload images in folder
        if (isset($result['response']) && $result['response'] == 200) {

            $arrayFormatName = array('large', 'medium', 'small', 'thumbnail');
            
            foreach($arrayFormatName as $value) {
                $this->imageOtherUpload($file, $value);
            }
            $this->imageOriginalUpload($file);
        }

        $result = array('success' => TRUE, 'response' => 200, 'responseDescription' => 'le fichier à bien été ajouter');
        return $result;
    }

    /**
     * crée et upload les images au format différent dans le dossier uploads
     */
    public function imageOtherUpload(array $file, string $formatSize) {
        $width = 0;
        $height = 0;
        $filename = $file['file']['new_name'];
      
        switch ($formatSize) {
            case 'large':
                $width = 1024;
                $height = 1024;
                $pathUploads = dirname(__FILE__, 4) . '/uploads/large';
            break;
            case 'medium':
                $width = 768;
                $height = 768;
                $pathUploads = dirname(__FILE__, 4) . '/uploads/medium';
                break;
            case 'small':
                $width = 360;
                $height = 360;
                $pathUploads = dirname(__FILE__, 4) . '/uploads/small';
                break;
            case 'thumbnail':
                $width = 150;
                $height = 150;
                $pathUploads = dirname(__FILE__, 4) . '/uploads/thumbnail';
                break;
                        
            default:
                $width = 1024;
                $height = 1024;
                $pathUploads = dirname(__FILE__, 4) . '/uploads/large';
                break;
        }

        $location =  $pathUploads.'/'.$filename;
        // $location =  $pathUploads.'/aaa.png';
        list($width_orig, $height_orig) = getimagesize($file['file']['tmp_name']);
        $ratio_orig = $width_orig/$height_orig;
        if ($width/$height > $ratio_orig) {
            $width = $height*$ratio_orig;
        } else {
            $height = $width/$ratio_orig;
        }

        $src = imagecreatefrompng($file['file']['tmp_name']);
        $dst = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        imagepng($dst, $location,0);
    }
 
    /**
     * crée et upload image dans le dossier uploads
     */
    public function imageOriginalUpload(array $file) {
            $pathUploads = dirname(__FILE__, 4) . '/uploads/original';
            $filename = $file['file']['new_name'];

            $location =  $pathUploads.'/'.$filename;
			// file extension
            $file_extension = pathinfo($location, PATHINFO_EXTENSION);
            $file_extension = strtolower($file_extension);
			// Valid extensions
			$valid_ext = array("jpg","png");
			
			$successUpload = 0;
			if(in_array($file_extension,$valid_ext)){
				// Upload file
				if(move_uploaded_file($file['file']['tmp_name'],$location)){
					$successUpload = 1;
                    
				} 
            }
    }

}