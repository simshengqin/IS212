<?php
require_once 'common.php';
require_once 'bootstrapValidate.php';

function doBootstrap() {
		

	$errors = array();
	$file_errors = [
		"course" => [],
		"section" => [],
		"student" => [],
		"courseCompleted" => [],
		"prerequisite" => [],
		"bid" => []
		
	];
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
				$row = 1;

				while (($course_data = fgetcsv($course))!== false){
					$file_errors['course'] = array_merge($file_errors['course'], validateCourse($course_data, $row));
					if (sizeof(validateCourse($course_data, $row)) == 0){
						$courseDAO->add($course_data[0], $course_data[1], $course_data[2], $course_data[3], 
										$course_data[4], $course_data[5], $course_data[6]);
						$lines_processed['course']++;
					} 
					$row++;
				}
				fclose($course);
				@unlink($course_path);

				$section_data = fgetcsv($section);
				$row = 1;
    			$allCoursesInfo = $courseDAO->retrieveAll();    // Get all course information (Course Class)
				while (($section_data = fgetcsv($section))!== false){
					$file_errors['section'] = array_merge($file_errors['section'], validateSection($section_data, $row, $allCoursesInfo));
					if (sizeof(validateSection($section_data, $row, $allCoursesInfo)) == 0){
						$sectionDAO->add($section_data[0], $section_data[1], $section_data[2], $section_data[3], 
										 $section_data[4], $section_data[5], $section_data[6], $section_data[7]);
						$lines_processed['section']++;
					}
					$row++;
				}
				fclose($section);
				@unlink($section_path);

				$student_data = fgetcsv($student);
				$row = 1;
				while (($student_data = fgetcsv($student))!== false){
					$allStudentInfo = $studentDAO->retrieveAll();
					$file_errors['student'] = array_merge($file_errors['student'], validateStudent($student_data, $row, $allStudentInfo));
					if (sizeof(validateStudent($student_data, $row, $allStudentInfo)) == 0){
						$studentDAO->add($student_data[0],  password_hash($student_data[1],PASSWORD_DEFAULT), 
						                 $student_data[2], $student_data[3], $student_data[4]);
						$lines_processed['student']++;
					}
					$row++;
				}
				fclose($student);
				@unlink($student_path);

				$prerequisite_data = fgetcsv($prerequisite);
				$row = 1;
				$allCourseInfo = $courseDAO->retrieveAll();
				while (($prerequisite_data = fgetcsv($prerequisite))!== false){
					$file_errors['prerequisite'] = array_merge($file_errors['prerequisite'], validatePrerequisite($prerequisite_data, $row, $allCourseInfo));
					if(sizeof(validatePrerequisite($prerequisite_data, $row, $allCourseInfo))==0){
						$prerequisiteDAO->add($prerequisite_data[0], $prerequisite_data[1]);
						$lines_processed['prerequisite']++;
					}
					$row++;
				}
				fclose($prerequisite);
				@unlink($prerequisite_path);

				$courseCompleted_data = fgetcsv($courseCompleted);
				$row = 1;
				while (($courseCompleted_data = fgetcsv($courseCompleted))!== false){
					$courseCompletedDAO->add($courseCompleted_data[0], $courseCompleted_data[1]);
					$lines_processed['courseCompleted']++;
				}
				fclose($courseCompleted);
				@unlink($courseCompleted_path);


				$bid_data = fgetcsv($bid);
				$row = 1;
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
			],
			"errors-found" => [
				"course.csv" => $file_errors["course"],
				"section.csv" => $file_errors["section"],
				"student.csv" => $file_errors["student"],
				"courseCompleted.csv" => $file_errors["courseCompleted"],
				"bid.csv" => $file_errors["bid"],
				"prerequisite.csv" => $file_errors["prerequisite"],	
			]
		];
		
		// $result = json_encode($result, JSON_PRETTY_PRINT);
		echo "Number of Records loaded: <br>";
		foreach ($result["num-record-loaded"] as $file => $line){
			echo " $file : $line <br>";
		}
		var_dump($result["errors-found"]);
		foreach ($result["errors-found"] as $file => $line){
			if (sizeof($line)>0){
				echo "Errors for $file <br>";
			
				foreach ($line as $linerow => $rows){
					echo "$linerow ";
					echo implode(', ', $rows);
					echo "<br>";
				}
				echo "---------------------------- <br>";
			}	
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