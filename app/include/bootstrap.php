<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$lines_processed = [
		"course" => 0,
		"section" => 0,
		"student" => 0,
		"courseCompleted" => 0,
		"prerequisite" => 0,
		"bid" =>0 
	];

	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0)
		$errors[] = "input files not found";

	else {
		
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);

		if ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
			
			# setting up the path for the required files
			$course_path = "$temp_dir/course.csv";
			$section_path = "$temp_dir/section.csv";
			$student_path = "$temp_dir/student.csv";
			$courseCompleted_path = "$temp_dir/course_completed.csv";
			$prerequisite_path = "$temp_dir/prerequisite.csv";
			$bid_path = "$temp_dir/bid.csv";
		
			
			# open the required files
			$course = @fopen($course_path, 'r');
			$section = @fopen($section_path, 'r');
			$student = @fopen($student_path, 'r');
			$courseCompleted = @fopen($courseCompleted_path, 'r');
			$prerequisite = @fopen($prerequisite_path, 'r');
			$bid = @fopen($bid_path, 'r');

			$check_empty = (empty($course) || empty($section) || empty($student) 
							|| empty($courseCompleted) || empty($prerequisite) || empty($bid));

			
			if ($check_empty){
				$errors[] = "input files not found";
				if (!empty($course)){
					fclose($course);
					@unlink($course_path);
				} 
				
				if (!empty($section)) {
					fclose($section);
					@unlink($section_path);
				}
				
				if (!empty($student)) {
					fclose($student);
					@unlink($student_path);
				}

				if (!empty($courseCompleted)) {
					fclose($courseCompleted);
					@unlink($courseCompleted_path);
				}

				if (!empty($prerequisite)) {
					fclose($prerequisite);
					@unlink($prerequisite_path);
				}

				if (!empty($bid)) {
					fclose($bid);
					@unlink($bid_path);
				}
				
				
			}
			else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection();

				# start processing
				# truncate current SQL tables
				$courseDAO = new CourseDAO();
				$courseDAO->removeAll();

				$sectionDAO = new SectionDAO();
				$sectionDAO->removeAll();

				$studentDAO = new StudentDAO();
				$studentDAO->removeAll();

				$courseCompletedDAO = new CourseCompletedDAO();
				$courseCompletedDAO->removeAll();

				$bidDAO = new BidDAO();
				$bidDAO->removeAll();

				$prerequisiteDAO = new PrerequisiteDAO();
				$prerequisiteDAO->removeAll();




				# then read each csv file line by line (remember to skip the header)
				# $data = fgetcsv($file) gets you the next line of the CSV file which will be stored 
				# in the array $data
				# $data[0] is the first element in the csv row, $data[1] is the 2nd, ....
				# process each line and check for errors

				$course_data = fgetcsv($course);
				
				while (($course_data = fgetcsv($course))!== false){
					$courseDAO->add($course_data[0], $course_data[1], $course_data[2], $course_data[3], 
									$course_data[4], $course_data[5], $course_data[6]);
					$lines_processed['course']++;
				}
				fclose($course);
				@unlink($course_path);

				$section_data = fgetcsv($section);
				
				while (($section_data = fgetcsv($section))!== false){
					$sectionDAO->add($section_data[0], $section_data[1], $section_data[2], $section_data[3], 
									$section_data[4], $section_data[5], $section_data[6], $section_data[7]);
					$lines_processed['section']++;
				}
				fclose($section);
				@unlink($section_path);

				$student_data = fgetcsv($student);
				
				while (($student_data = fgetcsv($student))!== false){
					$studentDAO->add($student_data[0],  password_hash($student_data[1],PASSWORD_DEFAULT), $student_data[2], $student_data[3], $student_data[4]);
					$lines_processed['student']++;
				}
				fclose($student);
				@unlink($student_path);



				$courseCompleted_data = fgetcsv($courseCompleted);
				
				while (($courseCompleted_data = fgetcsv($courseCompleted))!== false){
					$courseCompletedDAO->add($courseCompleted_data[0], $courseCompleted_data[1]);
					$lines_processed['courseCompleted']++;
				}
				fclose($courseCompleted);
				@unlink($courseCompleted_path);


				$prerequisite_data = fgetcsv($prerequisite);
				
				while (($prerequisite_data = fgetcsv($prerequisite))!== false){
					$prerequisiteDAO->add($prerequisite_data[0], $prerequisite_data[1]);
					$lines_processed['prerequisite']++;
				}
				fclose($prerequisite);
				@unlink($prerequisite_path);



				$bid_data = fgetcsv($bid);
				
				while (($bid_data = fgetcsv($bid))!== false){
					$bidDAO->add($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
					$lines_processed['bid']++;
				}
				fclose($bid);
				@unlink($bid_path);


				
			}
		}
	}

	# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

	if (!isEmpty($errors))
	{	
		$sortclass = new Sort();
		$errors = $sortclass->sort_it($errors,"bootstrap");
		$result = [ 
			"status" => "error",
			"messages" => $errors
		];
	}

	else
	{	
		$result = [  
			"status" => "success",
			"num-record-loaded" => [
				"course.csv" => $lines_processed["course"],
				"section.csv" => $lines_processed["section"],
				"student.csv" => $lines_processed["student"],
				"courseCompleted.csv" => $lines_processed["courseCompleted"],
				"bid.csv" => $lines_processed["bid"],
				"prerequisite.csv" => $lines_processed["prerequisite"],
				// "pokemon_type.csv" => $pokemon_type_processed,
				// "User.csv" => $User_processed
			]
		];
		
		// $result = json_encode($result, JSON_PRETTY_PRINT);
		foreach ($result["num-record-loaded"] as $file => $line){
			echo " $file : $line <br>";
		}
		echo "
		<br>
		<form method='get' action='include/admin.php'>
		<button type='submit'>Click me for next page</button>
		</form>";
		// header('location: admin.php');
	}
	
	 

}

?>