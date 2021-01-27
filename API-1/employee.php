<?php
  header("Access-Control-Allow-Origin: *");
class allProp {
  protected $db;
  protected $sql;
  protected $data = [];
  protected $response;
  function __construct(){
     $this->db = new mysqli("localhost","root","","employees");
     if($this->db->connect_error){
         echo "database connection failed";
     }
  }
}

 class mainclass{
     private $other;
     private $method;
     function __construct()
     {
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->other = new coder(); 
        if($this->method == "get" || $this->method == "GET"){
            $this->other->geter(); 
        } 

        if($this->method == "post" || $this->method == "POST"){
           $this->other->poster(); 
        } 

        if($this->method == "put" || $this->method == "PUT"){
           $this->other->puter(); 
        } 

        if($this->method == "delete" || $this->method == "DELETE"){
           $this->other->deleter(); 
           
        } 
     }
    
 }
$mango = new mainclass();

// start  request action code
class coder extends allProp{
   // start get request code here
   function geter(){
         $this->sql = "SELECT * FROM empdata";
         $this->sql = $this->db->query($this->sql);
         if($this->sql->num_rows != 0){
             while($this->response = $this->sql->fetch_assoc()){
                array_push($this->data,$this->response);
             }
             http_response_code(200);
          echo json_encode($this->data);
         }
         else{
             http_response_code(404);
            echo json_encode(array("status"=>"No data in database"));
         }
   } 
   // end get request code
   // start post code here
   function poster(){
     if(isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["address"])){
       $name = trim($_POST["name"]);
       $name = htmlspecialchars($name);
       $name = addslashes($name);
       $email = trim($_POST["email"]);
       $email = htmlspecialchars($email);
       $email = addslashes($email);
       $address = trim($_POST["address"]);
       $address = htmlspecialchars($address);
       $address = addslashes($address);
       $this->sql = "INSERT empdata(`name`,email,`address`)VALUES('$name','$email','$address')";
       if($this->db->query($this->sql)){
           http_response_code(201);
           echo json_encode(array("status"=>"data inserted successful"));

       }
       else{
            http_response_code(500);
            echo json_encode(array("status"=>"something is wrong"));
       }
     }
     else{
         http_response_code(500);
         echo json_encode(array("status"=>"Opps!  bad data sent"));
     }
   }
   //end post request code
   // start put request code here
   function puter(){
         $updatedata = $this->putcontent();
        if($updatedata != "" && $updatedata != "wrong" && $this->response != ""){
           $this->sql = "UPDATE empdata SET ".$updatedata." WHERE email = '$this->response'";
           if($this->db->query($this->sql)){
            http_response_code(202);
            echo json_encode(array("status"=>"successfuly updated data."));
           }
           else{
               http_response_code(500);
               echo json_encode(array("status"=>"opps! email is wrong"));
           }
        }
        else{
            http_response_code(500);
            echo json_encode(array("status"=>"opps! something wrong data sent"));
        }
   }


// start  delete request code here 
   function deleter(){
       
    $updatedata = $this->putcontent();
   if($this->response != ""){
      $this->sql = "DELETE FROM `empdata` WHERE `email`='$this->response'";
      $this->db->query($this->sql);
      if($this->db->affected_rows != 0){
       http_response_code(202);
       echo json_encode(array("status"=>"successfuly deleted ". $this->response));
      }
      else{
          http_response_code(500);
          echo json_encode(array("status"=>"opps! id is missing"));
      }
   }
   else{
       http_response_code(500);
       echo json_encode(array("status"=>"opps! Id is missing"));
   }
   }
// end delete request code here

   //start put content here
   function putcontent(){
       $colName = ["name","email","address"];
       $data = file_get_contents("php://input");
       $data = explode(";",$data);
       $retdata = "";
       for($i=1;$i<count($data);++$i){
           $data2 = explode('"',explode("----------------------------",$data[$i])[0]);
           $data2[2] = addslashes(htmlspecialchars(trim($data2[2])));
           if($data2[1] == "email"){
              if(strpos($data2[2], "--") !== false){
                 $betdata = explode("--", $data2[2]);
               $this->response = trim($betdata[0]);
              }
              else{
              $this->response = $data2[2];
              }
           }
           else{
           if(in_array($data2[1],$colName)){
               if (strpos($data2[2], "--") !== false) {
                  $betdata = explode("--", $data2[2]);
                  $data2[2] = trim($betdata[0]);
               }
              if($retdata == ""){
                $retdata = $data2[1]."='".$data2[2]."'";
              }
              else{
                $retdata .= ', '.$data2[1]. "='".$data2[2]."'";
              }
           }
           else{
                $retdata = "wrong";
                break;
           }
       }
    }
      return $retdata;
   } 
}
?>