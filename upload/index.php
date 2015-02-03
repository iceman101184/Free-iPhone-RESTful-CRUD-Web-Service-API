<?php
	include("../preheader.php");

	define('IMAGE_UPLOAD_DIR', "images"); //for image uploads

	if (isset($_FILES['image']) && $_FILES['image'] != ""){
		$img  = $_FILES['image'];
		uploadImage($img);
	}
	else{
		displayForm();
	}

	function uploadImage($imageObj){
		$imgUploadFolderRelative = "../" . IMAGE_UPLOAD_DIR . "/";

		if ($imageObj){
			$imgName = basename($imageObj['name']);
			$imgSize = $imageObj['size'];
			$allowed_ext = "jpg,jpeg,gif,png,bmp";
			$match = "0";
			if($imgSize > 0) {
				$img_ext = preg_split("/\./", $imgName);
				$allowed_exts = preg_split("/\,/", $allowed_ext);
				foreach ($allowed_exts as $ext) {
					if ($ext == strtolower(end($img_ext))) {
						$match = "1"; // File is allowed
						$tmp_file = $imageObj['tmp_name'];
						$newFilename = date("Ymdhi") . rand() . "_" . make_filename_safe($imgName);
						$img_path = $imgUploadFolderRelative . $newFilename;
						if (!file_exists($img_path)) {
							if (move_uploaded_file($tmp_file, $img_path) == false) {
								die(json_encode(array("error" => "Error uploading image. Please check filesize.")));
							}
							else {
								echo json_encode(array("success" => "Image uploaded successfully", "imageName" => $newFilename, "fullPath" => SITE_URL . "/" . IMAGE_UPLOAD_DIR . "/" . $newFilename));
							}//else
						}//if file doesn't exist
						else{
							die(json_encode(array("error" => "Error uploading image. File already exists.")));
						}
					}//if
				}//foreach
			}//if imageSize > 0
		}//if a file was uploaded

	}//uploadImage

	function make_filename_safe($filename){
		$filename = trim(str_replace(" ","_",$filename));
		$filename = str_replace("'", "", $filename);
		$filename = str_replace('"', '', $filename);

		return stripslashes($filename);
	}

	function displayForm(){
		echo <<< EOT
			<html>
				<head></head>
				<body>
					<form method='post' enctype="multipart/form-data" action="">
						Image: <input name="image" type="file" />
						<input type="submit" value="submit" />
					</form>
				</body>
			</html>
EOT;
	}

?>