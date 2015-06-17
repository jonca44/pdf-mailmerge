<?php	
/*
 * jQuery File Upload Plugin PHP Example 5.2.9.subfolders
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */
 
//error_reporting(E_ALL | E_STRICT);
require ("../../datasources/parsecsv/parsecsv.lib.php");
 
ini_set('memory_limit','1512M');
set_time_limit(120);
  
function getCurrentDateTime() {
	return gmdate('Y-m-d H:i:s');
}

//For the PDO, bind to an array.
function stmt_bind_assoc (&$stmt, &$out) {
    $data = mysqli_stmt_result_metadata($stmt);
    $fields = array();
    $out = array();

    $fields[0] = $stmt;
    $count = 1;

    while($field = mysqli_fetch_field($data)) {
        $fields[$count] = &$out[$field->name];
        $count++;
    }    
    @call_user_func_array(mysqli_stmt_bind_result, $fields);
}

 
class UploadHandler
{
    private $options;
	private $session;
    
    function __construct($options=null, $clear_image_versions=0) {
		global $session;
		
		$this->session = $session;
				
        $this->options = array( 
            'script_url' => $_SERVER['PHP_SELF'],
            'upload_dir' => dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/files/',
            'upload_url' => dirname($_SERVER['PHP_SELF']).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/files/',
            'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/\.(png|jpe?g|gif|docx?|pdf)$/i',
            'max_number_of_files' => null,
            'discard_aborted_uploads' => true,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname(__FILE__).'/files/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200
                ),
                */
				'template' => array( //Will be the image we use for our PDF creation page background.
					'type'		 => 'template',
                    'upload_dir' => dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/templates/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/templates/',
					'max_width' => 2480,
					'max_height' => 3508
                ),
				
				'preview' => array( //Used for our image previews in the admin panel.
					'type'		 => 'preview',
                    'upload_dir' => dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/previews/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/previews/',
                    'max_width' => 500,
                    'max_height' => 500
                ),
				
                'thumbnail' => array( //Used as a thumbnail in the admin panel.
					'type'		 => 'thumbnail',
                    'upload_dir' => dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/thumbnails/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80
                )
				
				
            )
        );
		
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
		
		if($clear_image_versions) {
			$this->options['image_versions'] = array();
		}
    }
    
    private function get_file_object($file_name, $subdir='', $datasourceData) {
        $suburl = str_replace('%2F', '/', rawurlencode($subdir));
        $file_path = $this->options['upload_dir'].$subdir.$file_name;
				
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].$suburl.rawurlencode($file->name);
			
			if(isset($datasourceData)) {
				//print_r($datasourceData[$file_path]);
				$headerData = json_decode($datasourceData[$file_path]['headers'], 1);
				$headerData = isset($headerData) ? implode(", ", $headerData ) : "";
				$file->headers = $headerData;
				$file->lines = $datasourceData[$file_path]['lines'];
			}
			
            foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$subdir.$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url'].$suburl.rawurlencode($file->name);
                } else if (is_file($options['upload_dir'].$subdir.$file_name.".png")) { //Called for PDFs we've made thumbs for.
                    $file->{$version.'_url'} = $options['upload_url'].$suburl.rawurlencode($file->name.".png");
					$file->{'pdf_pages'}     = $this->pdf_page_count($file_name, $subdir);
					$file->{$version.'_folder'}    = $options['upload_url'].$suburl;
                }
            }
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name)
                .'&subdir='.rawurlencode($subdir);
            $file->delete_type = 'DELETE';
			$file->subdir = $subdir;
            return $file;
        }
        return null;
    }


	//Leon - 01/07/13
	//Order the directory by date modified.
	//http://stackoverflow.com/questions/11923235/scandir-to-sort-by-date-modified
  private function scan_dir($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess');

    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

    return ($files) ? $files : array();
}
    
    private function get_file_objects($list=array(), $subdir='', $datasourceData = null) {
        $upload_dir = $this->options['upload_dir'].$subdir;
		
		//Create hd directory if it doesn't exist yet.
		if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0700, true);
        }
		
        foreach ($this->scan_dir($upload_dir) as $file_name) {
            if ($file_name[0] !== '.') {
                $file_path = $upload_dir.$file_name;
                if (is_file($file_path)) {
                    array_push($list, $this->get_file_object($file_name, $subdir, $datasourceData));
                } elseif (is_dir($file_path)) {
                    $list = $this->get_file_objects($list, $subdir.$file_name.'/', $datasourceData);
                }
            }
        }
        return $list;
    }
	
	private function pdf_page_count($file_name, $subdir='') {
		$template_file_dir  = dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/templates/'.$subdir;
		
		$files = array_diff(scandir($template_file_dir), array('..', '.'));
		
		return( count($files));
	}
	
	//
	private function create_pdf_images($file_name, $options, $subdir='') {
		
		$file_path =  $this->options['upload_dir'].$subdir.$file_name;	//Location of the original PDF.
        $template_file_path = $options['upload_dir'].$subdir."%d.png"; 	//Used for PDF -> PNG template originals.
					
		$template_file_pg1  = dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/templates/'.$subdir."1.png"; 
		$thumb_file_path = $options['upload_dir'].$subdir.$file_name.".png";	//Used to build a thumbnail preview.
		
		$template_file_dir  = dirname(__FILE__).'/user_files/'.$this->session->get_user_var('file_directory').'/backgrounds/templates/'.$subdir; 		//Location of the template files generated originally by Ghostscript.
		$out_file_dir 		= $options['upload_dir'].$subdir;
		
		//Create the directory for the file if it doesn't exist.
		if (!is_dir($options['upload_dir'].$subdir)) {
	            mkdir($options['upload_dir'].$subdir, 0700, true);
        	}
		
		//If we're creating template files.
		if($options['type'] == 'template') {
			//pngalpha
			//gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -dGraphicsAlphaBits=4 -r300x300 -dFirstPage=1 -dLastPage=20
			
			//User uploaded a docx, convert it to PDF, then to PNG images.
			if( preg_match("/^(docx?)$/", strtolower(substr(strrchr($file_name, '.'), 1))) ) {
				
				$sh_command = 'sudo '.__DIR__.'/processDocX.sh '.escapeshellarg($this->options['upload_dir'].$subdir).' '.escapeshellarg($file_path);
				$ret_val = 0;
				exec($sh_command, $output, $ret_val);				 
				$file_path = preg_replace('/(docx?)$/', 'pdf', $file_path); //Update the filepath to change .docx extension to .pdf.
				
			}
			
			$sh_command = 'gs -dQuiet -dSAFER -dBATCH -dNOPAUSE -dNOPROMT -dMaxBitmap=500000000 -dAlignToPixels=0 -dGridFitTT=2 -sDEVICE=pngalpha -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -r300x300 -dFirstPage=1 -dLastPage=20 -sOutputFile='.escapeshellarg($template_file_path).' -f'.escapeshellarg($file_path);
			$ret_val = 0;
			
		//	echo "Proc Sh ".$sh_command;
			//Convert the PDF into PNG Images, 1 per page.
			exec($sh_command, $output, $ret_val);
			unlink($file_path); //Remove the temporary .pdf conversion of the .docx
		
		//Create the first page preview file.
		} else if($options['type'] != 'template') {		
			list($img_width, $img_height) = getimagesize($template_file_pg1);
			if (!$img_width || !$img_height) {
				return false;
			}
			
			//If no scale max height or width was set, set them to the same as the image so that no scaling is done. Scale = 1.
			if(!isset( $options['max_width']) || !isset( $options['max_height'])) {
				$options['max_width']  = $img_width;
				$options['max_height'] = $img_height;
			}
			
			$scale = min(
				$options['max_width'] / $img_width,
				$options['max_height'] / $img_height
			);
			
			//If the scale is > 1 set the scale to 1 so as to not resize the image.
			if ($scale > 1) {
				$scale = 1;
			}
			
			$new_width = $img_width * $scale;
			$new_height = $img_height * $scale;
			$new_img = imagecreatetruecolor($new_width, $new_height);
			
			@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
			@imagealphablending($new_img, false);
			@imagesavealpha($new_img, true);
			$src_img = @imagecreatefrompng($template_file_pg1);
			$write_image = 'imagepng';				
				   
			$success = $src_img && imagecopyresampled(
				$new_img,
				$src_img,
				0, 0, 0, 0,
				$new_width,
				$new_height,
				$img_width,
				$img_height
			) && $write_image($new_img, $thumb_file_path);
			
			// Free up memory (imagedestroy does not delete files):
			@imagedestroy($src_img);
			@imagedestroy($new_img);
		} 
			
			
		//if($options['type'] != 'template') {
		//Generate a resized base image for each page.
		$files = array_diff(scandir($template_file_dir), array('..', '.'));
		foreach($files as $filename) {
				 
			list($img_width, $img_height) = getimagesize($template_file_dir.$filename);
			if (!$img_width || !$img_height) {
				return false;
			}
			
			//If no scale max height or width was set, set them to the same as the image so that no scaling is done. Scale = 1.
			if(!isset( $options['max_width']) || !isset( $options['max_height'])) {
				$options['max_width']  = $img_width;
				$options['max_height'] = $img_height;
			}
			
			$scale = min(
				$options['max_width'] / $img_width,
				$options['max_height'] / $img_height
			);
			
			//If the scale is > 1 set the scale to 1 so as to not resize the image.
			if ($scale > 1) {
				$scale = 1;
			}
			
			$new_width = $img_width * $scale;
			$new_height = $img_height * $scale;
			$new_img = imagecreatetruecolor($new_width, $new_height);
			
			@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
			@imagealphablending($new_img, false);
			@imagesavealpha($new_img, true);
			$src_img = @imagecreatefrompng($template_file_dir.$filename);
			$write_image = 'imagepng';				
				   
			$success = $src_img && imagecopyresampled(
				$new_img,
				$src_img,
				0, 0, 0, 0,
				$new_width,
				$new_height,
				$img_width,
				$img_height
			) && $write_image($new_img, $out_file_dir.$filename);
			
			// Free up memory (imagedestroy does not delete files):
			@imagedestroy($src_img);
			@imagedestroy($new_img);	 
		 
		}
		//}
        return 1;	
		
	}
	
	
    private function create_scaled_image($file_name, $options, $subdir='') {
        $file_path = $this->options['upload_dir'].$subdir.$file_name;
        $new_file_path = $options['upload_dir'].$subdir.$file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        if (!is_dir($options['upload_dir'].$subdir)) {
            mkdir($options['upload_dir'].$subdir, 0700, true);
        }
		
		//If no scale max height or width was set, set them to the same as the image so that no scaling is done. Scale = 1.
		if(!isset( $options['max_width']) || !isset( $options['max_height'])) {
			$options['max_width']  = $img_width;
            $options['max_height'] = $img_height;
		}
		
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
		
		//If the scale is > 1 or if not height was set, set the scale to 1 so as to not resize the image.
        if ($scale > 1) {
            $scale = 1;
        }
		
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                break;
            default:
                $src_img = $image_method = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }
    
    private function has_error($uploaded_file, $file, $error) {
        if ($error) {
            return $error;
        }
		
		//Used to check if the file of this name already exists.
		$subdir = $this->get_subdir($file->name);
		$file_path = $this->options['upload_dir'].$subdir.$file->name;		
		if(is_file($file_path)) {
			return "Sorry, this file already exists. Please rename your file and upload it again.";
		}
		
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
			if(strstr($this->options['accept_file_types'], "csv"))
				return 'Sorry, your file type must be .csv';
			else 
				return 'Sorry, your file type must be png, jpeg, jpg, gif, doc, docx or pdf';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }
    
    private function trim_file_name($name, $type) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }
        return $file_name;
    }

    private function is_valid_subdir($subdir, $upload_dir=null) {
        if (!$upload_dir) {
            $upload_dir = $this->options['upload_dir'];
        }
        $real_upload_dir = realpath($upload_dir);
        $real_subdir = realpath($upload_dir.$subdir);
        if (substr($real_subdir, 0, strlen($real_upload_dir)) === $real_upload_dir) {
            return true;
        }
        return false;
    }
    
    private function delete_empty_subdirs($subdir, $upload_dir=null) {
        if (!$upload_dir) {
            $upload_dir = $this->options['upload_dir'];
        }
        $real_upload_dir = realpath($upload_dir);
        $real_subdir = realpath($upload_dir.$subdir);
        $strlen_real_upload_dir = strlen($real_upload_dir);
        $success = true;
        while ($success && strlen($real_subdir) > $strlen_real_upload_dir) {
            $success = @rmdir($real_subdir);
            $real_subdir = dirname($real_subdir);
        }
    }
    
    private function get_subdir($file_name) {
        return implode('/', array_reverse(explode('.', $file_name))).'/';
    }
    
    private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type);
        $file->size = intval($size);
        $file->type = $type;
        $error = $this->has_error($uploaded_file, $file, $error);
        if (!$error && $file->name) {
            $subdir = $this->get_subdir($file->name);
            if (!is_dir($this->options['upload_dir'].$subdir)) {
                mkdir($this->options['upload_dir'].$subdir, 0700, true);
            }
            $file_path = $this->options['upload_dir'].$subdir.$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] && is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
			$file_details = pathinfo($file_path);
            if ($file_size === $file->size) {
                $suburl = str_replace('%2F', '/', rawurlencode($subdir));
                $file->url = $this->options['upload_url'].$suburl.rawurlencode($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
					if(preg_match("/^(docx?|pdf)$/", strtolower(substr(strrchr($file->name, '.'), 1)) )) {
						if ($this->create_pdf_images($file->name, $options, $subdir)) {
							$file->{$version.'_url'} = $options['upload_url'].$suburl.rawurlencode($file->name.".png");
							$file->{'pdf_pages'} = $this->pdf_page_count($file->name, $subdir);
							$file->{$version.'_folder'}    = $options['upload_url'].$suburl;
						}
					} else {
						if ($this->create_scaled_image($file->name, $options, $subdir)) {
							$file->{$version.'_url'} = $options['upload_url'].$suburl.rawurlencode($file->name);
						}
					}
					
					//Insert a record for this page into our database.
					if($options['type'] == 'template'){
						$filenames = array();
						if(preg_match("/^(docx?|pdf)$/", strtolower(substr(strrchr($file->name, '.'), 1)) )) {
							for($c = 1; $c <= $file->{'pdf_pages'}; $c++) {
								$filenames[] = $c.".png";
							}
						} else {
							$filenames[] = $file->name;
						}
						
						$this->createDBPageBackground($file->name, $options, $subdir, $filenames);
					}
                }
				
				//Create datasources from the DB. They're files that end in CSV.
				if(isset($file_details['extension']) && $file_details['extension'] === "csv") {
					$csvData = $this->createDBDataSource($file->name, $this->options, $subdir, $file_path);
					
					$file->headers = $csvData['headers'];
					$file->lines = $csvData['lines'];
				}
				
				
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
			
            $file->size = $file_size;
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name)
                .'&subdir='.$subdir;
            $file->delete_type = 'DELETE';
			$file->subdir = $subdir;
        } else {
            $file->error = $error;
        }
        return $file;
    }
	
	
	private function createDBPageBackground($filename, $options, $subdir, $filenames) {
	
		global $session, $sql;
		
		$query = "INSERT INTO backgrounds (created_at, updated_at, user_id, name, data_path)
				  VALUES(?, NOW(), ?, ?, ?)";
		
		$stmt = $sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $sql->link->error);
		} else {
		
			$subdirSQL = $options['upload_url'].$subdir;
			$time = getCurrentDateTime();
			$userID = $this->session->get_user_var('id');
			
			$stmt->bind_param('siss', $time, $userID, $filename, $subdirSQL);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows = $stmt->affected_rows;
				$backgroundID = $stmt->insert_id; 

					//Loop through the filenames associated with this document in order and insert them into the DB associated with this background.
					foreach($filenames as $key => $filename) {
						$query = "INSERT INTO backgrounds_pages (created_at, updated_at, background_id, background_pg_num, file_name)
								  VALUES(?, NOW(), ?, ?, ?)";
				
						$stmt = $sql->link->prepare($query);
						if (!$stmt) {
						  die('Invalid query: ' . $sql->link->error);
						} else {					
							$time   = getCurrentDateTime();
							$pg_num = $key+1;
							
							$stmt->bind_param('siis', $time, $backgroundID, $pg_num, $filename);
							$resultFromExec = $stmt->execute();
						}					 
					}
			}
			
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}
	
	}
	
	private function deleteDBPageBackground($filename, $options, $subdir) {
	
		global $session, $sql;
		
		$query = "UPDATE backgrounds 
				  INNER JOIN backgrounds_pages on backgrounds.id = backgrounds_pages.background_id
				  SET backgrounds.deleted_at = NOW(), backgrounds_pages.deleted_at = NOW()
				  WHERE backgrounds.data_path = ? and backgrounds.user_id = ?
				  AND backgrounds.deleted_at is null";
		
		$stmt = $sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $sql->link->error);
		} else {
		
			$subdirSQL = $options['upload_url'].$subdir;
			$userID = $this->session->get_user_var('id');
			
			$stmt->bind_param('si', $subdirSQL, $userID);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows = $stmt->affected_rows;
			}
			
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}
	}
	
	private function createDBDataSource($filename,  $options, $subdir, $file_path) {
		global $session, $sql;
		
		$query = "INSERT INTO datasources (created_at, updated_at, user_id, name, data_path, file_name, `headers`, `lines`)
				  VALUES(?, NOW(), ?, ?, ?, ?, ?, ?)";
		
		$stmt = $sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $sql->link->error);
		} else {
		
			$csv = new parseCSV();
			$realUserPath = realpath($file_path);
			
			if(filesize($realUserPath) > 0) {
				$csv->parse($realUserPath, 0 , 10000); // At max 10000 lines.				
				$csvDataRows = $csv->unparse($csv->data, $csv->titles, null, null, null, true);			
			} else {
				$csvDataRows = array(array(""));
			}
					
			$lines = count($csvDataRows) - 1;
			$headers = json_encode($csv->titles);
			$subdirSQL = $options['upload_url'].$subdir;
			$time = getCurrentDateTime();
			$userID = $this->session->get_user_var('id');
			
			$stmt->bind_param('sissssi', $time, $userID, $filename, $subdirSQL, $filename, $headers, $lines);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows = $stmt->affected_rows;
			}
			
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}
		
		return( array("lines" => $lines, "headers" => implode(", ",array_filter($csv->titles)) ) ); 
	}
	
	private function deleteDBDataSource($filename, $options, $subdir) {
		global $session, $sql;
		
		$query = "UPDATE datasources 
				  SET datasources.deleted_at = NOW()
				  WHERE datasources.data_path = ? and datasources.user_id = ? and datasources.file_name = ?
				  AND datasources.deleted_at is null";
				  
		$stmt = $sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $sql->link->error);
		} else {
		
			$subdirSQL = $options['upload_url'].$subdir;
			$userID = $this->session->get_user_var('id');
			
			$stmt->bind_param('sis', $subdirSQL, $userID, $filename);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows = $stmt->affected_rows;
			}
			
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}
	}
	
    
    public function get() {

		global $session, $sql;
		$datasourceData = array();
		$returnResults = array();
			
		//If we're doing a datasource lookup, fetch the headers and line count from the SQL database.
		if(strstr($this->options['upload_dir'], "/datasources/")) {
		
			$userID = $session->get_user_var('id');
			
			$query = "SELECT `name`, `data_path`, `file_name`, `headers`, `lines`
					  FROM datasources
					  WHERE user_id = ? 
					  AND deleted_at is null";
		
			$stmt = $sql->link->prepare($query);
			if (!$stmt) {
			  die('Invalid query: ' . $sql->link->error);
			} else {
			
				$stmt->bind_param('i', $userID);
				$resultFromExec = $stmt->execute();
				$stmt->store_result();
				stmt_bind_assoc($stmt, $returnResults);
				
			
				// loop through all result rows
				while ($stmt->fetch()) {
					foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
					} 
					$datasourceData[$_SERVER['DOCUMENT_ROOT'].$returnResults['data_path'].$returnResults['file_name']] = $row_tmb;				
				}
			}
		} else {
			$datasourceData = null;
		}
		
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $subdir = isset($_REQUEST['subdir']) ?
            stripslashes($_REQUEST['subdir']) : null;
					
        if (!($subdir && $this->is_valid_subdir($subdir))) {
            $subdir = '';
        }
				
		//Check to ensure they have a home directory set.
		if($this->session->get_user_var('file_directory') == "") {
			header('Content-type: application/json');
			echo json_encode(array());
		} else {
			if ($file_name) {
				$info = $this->get_file_object($file_name, $subdir, null);
			} else {
				$info = $this->get_file_objects(array(), $subdir, $datasourceData);
			}
			header('Content-type: application/json');
			echo json_encode($info);
		}
    }
    
    public function post() {
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } elseif ($upload) {
            $info[] = $this->handle_file_upload(
                $upload['tmp_name'],
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                $upload['error']
            );
        }
        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo json_encode($info);
    }
    
    public function delete() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $subdir = isset($_REQUEST['subdir']) ?
            stripslashes($_REQUEST['subdir']) : null;
        if (!($subdir && $this->is_valid_subdir($subdir))) {
            $subdir = '';
        }
        $file_path = $this->options['upload_dir'].$subdir.$file_name;
		$file_details = pathinfo($file_path);
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            $this->delete_empty_subdirs($subdir);
			
			//Clear out the alternate version files & DB sql.
            foreach($this->options['image_versions'] as $version => $options) {
			
				if(is_dir($options['upload_dir'].$subdir)) {
					foreach (new DirectoryIterator($options['upload_dir'].$subdir) as $fileInfo) {
						if(!$fileInfo->isDot())
							unlink($fileInfo->getPathname());
					}
				}
				$this->delete_empty_subdirs($subdir, $options['upload_dir']);
				
				//delete the database records for this background and its pages.
				if($options['type'] == 'template'){
					$this->deleteDBPageBackground($file_name, $options, $subdir);
				}
            }
			
			//Delete datasources from the DB. They're files that end in CSV.
			if(!in_array($file_details['extension'], array('png', 'jpeg', 'jpg', 'gif', 'doc', 'docx','pdf'))) {
				$this->deleteDBDataSource($file_name, $this->options, $subdir);
			}
        }
        header('Content-type: application/json');
        echo json_encode($success);
    }
	
	
}
?>
