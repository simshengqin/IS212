<?php
require_once 'common.php';
require_once 'bootstrapValidate.php';
function trimWhitespace($data) {
	for($i = 0; $i < count($data); $i++) {
		$data[$i] = trim($data[$i], " ");
	}
	return $data;
}
function doBootstrap() {
		

	$errors = array();

	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$record['num-record-loaded'] = [
		"bid.csv" => 0,
		"course.csv" => 0,
		"courseCompleted.csv" => 0,
		"prerequisite.csv" => 0,
		"section.csv" => 0,
		"student.csv" => 0	
	];

	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0) {
		$errors[] = "input files not found";
		return $errors; 						// stop bootstrap process if there's no input files found
	}

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
	## Course ##
				$header = fgetcsv($course); //The first line is always the header
				$row = 2;
				while (($course_data = fgetcsv($course))!== false){
					$course_data = trimWhitespace($course_data);
					$course_data = str_replace(chr(160), "", $course_data);  // To remove 'invisible space' (\xA0) in course desc
					$commonValidation = commmonValidation($course_data, $row, $header, 'course.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation; //stores error 
					}
					else {
						$courseValidation = validateCourse($course_data, $row);					
						if (empty($courseValidation)){
							$courseDAO->add($course_data[0], $course_data[1], $course_data[2], $course_data[3], 
											$course_data[4], $course_data[5], $course_data[6]);
							$record['num-record-loaded']['course.csv']++;
						}
						else
							$errors[] = $courseValidation;
					} 
					$row++;
				}
				fclose($course);
				@unlink($course_path);

	## Section ##
				$header = fgetcsv($section);
				$row = 2;
    			$allCourseInfo = $courseDAO->retrieveAll();    // Get all course information (Course Class)
				while (($section_data = fgetcsv($section))!== false){
					$section_data = trimWhitespace($section_data);
					$commonValidation = commmonValidation($section_data, $row, $header, 'section.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation; //stores error 
					}
					else {
						$sectionValidation = validateSection($section_data, $row, $allCourseInfo);
						if (sizeof($sectionValidation) == 0){
							$sectionDAO->add($section_data[0], $section_data[1], $section_data[2], $section_data[3], 
											$section_data[4], $section_data[5], $section_data[6], $section_data[7]);
							$record['num-record-loaded']['section.csv']++;
						}
						else
							$errors[] = $sectionValidation;
					}
					$row++;
				}
				fclose($section);
				@unlink($section_path);

	## Student ##
				$header = fgetcsv($student);
				$row = 2;
				while (($student_data = fgetcsv($student))!== false){
					$student_data = trimWhitespace($student_data);
					$commonValidation = commmonValidation($student_data, $row, $header, 'student.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation; //stores error 
					}
					else {
						$allStudentInfo = $studentDAO->retrieveAll();     // Need put within while loop because we need to prevent duplicate student userid
						$studentValidation = validateStudent($student_data, $row, $allStudentInfo);
						if (sizeof($studentValidation) == 0){
							$studentDAO->add($student_data[0], $student_data[1], 
											$student_data[2], $student_data[3], $student_data[4]);
						$record['num-record-loaded']['student.csv']++;
						}
						else 
							$errors[] = $studentValidation;
					}
					$row++;
				}
				fclose($student);
				@unlink($student_path);

	## Prerequisite ##
				$header = fgetcsv($prerequisite);
				$row = 2;
				$allCourseInfo = $courseDAO->retrieveAll();			// Retrieve all course info to check prerequisite
				while (($prerequisite_data = fgetcsv($prerequisite))!== false){
					$prerequisite_data = trimWhitespace($prerequisite_data);
					$commonValidation = commmonValidation($prerequisite_data, $row, $header,'prerequisite.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation;  //stores error 
					}
					else {
						$prerequisiteValidation = validatePrerequisite($prerequisite_data, $row, $allCourseInfo);
						if(sizeof($prerequisiteValidation)==0){
							$prerequisiteDAO->add($prerequisite_data[0], $prerequisite_data[1]);
							$record['num-record-loaded']['prerequisite.csv']++;
						}
						else 
							$errors[] = $prerequisiteValidation;
					}
					$row++;
				}
				fclose($prerequisite);
				@unlink($prerequisite_path);

	## Course Completed ##
				$header = fgetcsv($courseCompleted);
				$row = 2;
				$allStudentInfo = $studentDAO->retrieveAll();			// Retrieve all student info to check if user id exist
				$allPrerequisiteInfo = $prerequisiteDAO->retrieveAll(); // Retrieve all prerequisite info to check prerequisite courses
				while (($courseCompleted_data = fgetcsv($courseCompleted))!== false){
					$courseCompleted_data = trimWhitespace($courseCompleted_data);
					$commonValidation = commmonValidation($courseCompleted_data, $row, $header, 'course_completed.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation;  //stores error 
					}
					else {
						$courseCompletedValidation = validateCourseCompleted($courseCompleted_data, $row, $allCourseInfo, $allStudentInfo, $allPrerequisiteInfo);
						if (sizeof($courseCompletedValidation)==0){
							$courseCompletedDAO->add($courseCompleted_data[0], $courseCompleted_data[1]);
							$record['num-record-loaded']['courseCompleted.csv']++;
						}
						else 
							$errors[] = $courseCompletedValidation;
					}
					$row++;
				}
				fclose($courseCompleted);
				@unlink($courseCompleted_path);

	
	## Bid ##
				$header = fgetcsv($bid);
				$row = 2;
				$edollarList = [];
				while (($bid_data = fgetcsv($bid))!== false){	
					$bid_data = trimWhitespace($bid_data);
					$commonValidation = commmonValidation($bid_data, $row, $header, 'bid.csv'); 
					if (!empty($commonValidation)) { //if input field is blank
						$errors[] =  $commonValidation; //stores error 
					}
					else {
						$sectionsInfo = $sectionDAO->retrieveSectionByFilter($bid_data[2]);  // Get section list by the course 		
						$bidValidation = validateBid($bid_data, $row, $allStudentInfo, $allCourseInfo, $sectionsInfo);		
						if (sizeof($bidValidation)==0){
							if (array_key_exists($bid_data[0], $edollarList))
								$edollarList[$bid_data[0]] += $bid_data[1];
							else
								$edollarList[$bid_data[0]] = $bid_data[1];
							$student = $studentDAO->retrieveStudent($bid_data[0]); // get student info
							if ($edollarList[$bid_data[0]] < $student->getEdollar()){   // compare total bid amount against student's edollar
								$bidDAO->add($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
								$record['num-record-loaded']['bid.csv']++;
							}
							else{
								$bidValidation[] = "not enough e-dollar";
							}
						}
						else {
							$errors[] = $bidValidation;
						}
					}
					$row++;
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
		$errors = $sortclass->sort_it($errors,"comp");				//sort by line then sort by title
		$temp = [];
		foreach ($record['num-record-loaded'] as $key => $value){
			$temp[] = [$key => $value];
		}
		$result = [ 
			"status" => "error",
			"num-record-loaded" => $temp,
			"error" => $errors
		];

		$result['status'] = 'error';
		$result['error'] = $errors;

		
		return $result;
	}
	else
	{	
		$temp = [];
		foreach ($record['num-record-loaded'] as $key => $value){
			$temp[] = [$key => $value];
		}
		$result = [  
			"status" => "success",
			"num-record-loaded" => $temp
		];
		return $result;

		// echo "Number of Records loaded: <br>";
		// foreach ($result["num-record-loaded"] as $file => $line){
		// 	echo " $file : $line <br>";
		// }
		// foreach ($result["errors-found"] as $file => $line){
		// 	if (sizeof($line)>0){
		// 		echo "<br>Errors for $file <br>";
			
		// 		foreach ($line as $linerow => $rows){
		// 			echo "$linerow ";
		// 			echo implode(', ', $rows);
		// 			echo "<br>";
		// 		}
		// 		echo "---------------------------- <br>";
		// 	}	
		// }
		// echo "
		// <br>
		// <form method='get' action='include/admin.php'>
		// <button type='submit'>Click me for next page</button>
		// </form>";

	}
	return $result;
}


?>