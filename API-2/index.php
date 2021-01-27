<?php
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: POST,GET,PUT,PATCH,DELETE");
  header("Content-Type: application/json");
  header("Access-Control-Allow-headers: Access-Control-Allow-headers,Content-Type,Access-Control-Allow-Methods,Authorization,X-Request-width");
  abstract class database{
      protected $db;
      protected $data = [];
      protected $response;
      protected $other;
      public function __construct(){
           $this->db = new mysqli("localhost","root","","employees");
           if($this->db->connect_error){
               die("Oops! Server has beed down.");
           }
      }

  }

  class main extends database{

    public function requesthttp():void{
        if(strtoupper($_SERVER["REQUEST_METHOD"]) == "GET"){
          $this->getdata();
        } else if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $this->postdata();
        } else if (strtoupper($_SERVER["REQUEST_METHOD"]) == "DELETE") {
            $this->deletedata();
        } else if (strtoupper($_SERVER["REQUEST_METHOD"]) == "PUT") {
            $this->putdata();
        } else if (strtoupper($_SERVER["REQUEST_METHOD"]) == "PATCH") {
           $this->patchdata();
        }
    }
    // start get method to access data from server
    private function getdata():void{
        $this->other = "SELECT * FROM empdata";
        $this->response = $this->db->query($this->other);
        if ($this->response->num_rows != 0) {
            while ($this->other = $this->response->fetch_assoc()) {
                array_push($this->data, $this->other);
            }
            http_response_code(200);
            echo json_encode($this->data);
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "No data in server"));
        }
        $this->db->close();
    }
    //start post method to insert data in server
    private function postdata():void{
        $name = addslashes(htmlspecialchars(trim($_POST["name"])));
        $email = addslashes(htmlspecialchars(trim($_POST["email"])));
        $address = addslashes(htmlspecialchars(trim($_POST["address"])));
        $this->other = $this->db->prepare("INSERT INTO empdata(name,email,address)VALUES(?,?,?)");
        $this->other->bind_param('sss',$name,$email,$address);
        if($this->other->execute()){
            http_response_code(201);
            echo json_encode(array("status" => "Successfuly data inserted"));
        }
        else{
            http_response_code(404);
            echo json_encode(array("status"=>"data not inserted"));
        }
        $this->other->close();
        $this->db->close();
    }
    // start put or push method to update in database
    private function putdata():void{
        $userid = json_decode(file_get_contents("php://input"), true);
        $email = $userid["email"];
        $email = addslashes(htmlspecialchars(trim($email)));
        $name = $userid["name"];
        $name = addslashes(htmlspecialchars(trim($name)));
        $address = $userid["address"];
        $address = addslashes(htmlspecialchars(trim($address)));
        $this->other = $this->db->prepare("UPDATE empdata SET name = ?, address = ? WHERE email = ?");
        $this->other->bind_param("sss", $name,$address,$email);
        $this->other->execute();
        if ($this->other->affected_rows != 0) {
            http_response_code(202);
            echo json_encode(array("status" => "Successful updated"));
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "Oops! did not update."));
        }
    }
    // start DELETE method to delete data form database
    private function deletedata():void
    {
        $userid = json_decode(file_get_contents("php://input"),true);
        $userid=$userid["email"];
        $userid= addslashes(htmlspecialchars(trim($userid)));
        $this->other = $this->db->prepare("DELETE FROM empdata WHERE email = ?");
        $this->other->bind_param("s",$userid);
        $this->other->execute();
        if($this->other->affected_rows != 0){
            http_response_code(202);
            echo json_encode(array("status"=>"Successful deleted"));
        }
        else{
            http_response_code(404);
            echo json_encode(array("status" => "Oops! did not delete."));
        }
    }

    // start patch method to fetch particular row from database
    private function patchdata():void{
        $userid = json_decode(file_get_contents("php://input"), true);
        $userid = $userid["email"];
        $userid = addslashes(htmlspecialchars(trim($userid)));
        $this->other = "SELECT * FROM empdata WHERE email = '$userid'";
        $this->other = $this->db->query($this->other);
        if ($this->other->num_rows != 0) {
            $this->other = $this->other->fetch_assoc();
            http_response_code(200);
            echo json_encode($this->other);
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "Oops! did not fetch."));
        }
    }
  }
  $ob = new main();
$ob->requesthttp();
?>