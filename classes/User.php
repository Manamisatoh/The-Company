<?php

/*
    include: will include the file every time you run the program
    include_once: will include the file once only

    require: will require or include the file, if not found it will stop the script
    require_once : will require once or include the file, if not found it sill stop the script

    //differences//
     require stops the script if it cannot find the file. _once gives up it after the first missing
*/

//child class

require_once 'Database.php'; // includes everytime refresh the page (once)

class User extends Database
{
    //store() : insert record
    public function store($request)
    {
        $first_name = $request['first_name'];
        $last_name  = $request['last_name'];
        $username   = $request['username'];
        $password   = $request['password'];

        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, username, password)
        VALUES ('$first_name', '$last_name', '$username', '$password')";

        if($this->conn->query($sql)) {
            header('location: ../views'); //go to index.php or the login page, automatically displays the index
            exit;                          // same as die   
        }else {
            die('Error crating the user: ' . $this->conn->error);
        }
    }

    public function login($request)
    {
        $username   = $request['username'];
        $password   = $request['password'];

        $sql ="SELECT * FROM users WHERE username= '$username'";

        $result = $this->conn->query($sql);

        //check the username
        //use an array "$user" and key
        if($result->num_rows ==1){
            $user = $result->fetch_assoc();

            if(password_verify($password, $user['password'])){
                //create session 
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['first_name'] . " " .$user['last_name'];

                header('location: ../views/dashboard.php' );
                exit;

            }else {
                die('Password  is not correct: ');
            }

        }else {
            die('Username not found: ');
        }

    }

    public function logout(){

        session_start();
        session_unset();
        session_destroy();

        header('location: ../views'); // index page
        exit;

    }

    public function getAllUsers(){
        $sql ="SELECT id, first_name, last_name, username, photo FROM users";

        if ($result = $this->conn->query($sql)){
            return $result;
        }else {
            die ('Error retrieving all users ' . $this->conn->error);
        }

    }

    public function getUser() {
        $id = $_SESSION['id'];

        $sql ="SELECT id, first_name, last_name, username, photo FROM users WHERE id= $id";

        if ($result = $this->conn->query($sql)){
            return $result->fetch_assoc();
        }else {
            die ('Error retrieving the user ' . $this->conn->error);
        }

    }

    public function update($request, $files) {
        session_start();
        $id =$_SESSION['id'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $username = $request['username'];
        $photo =$files['photo']['name']; // holds the name of the photo
        $tmp_photo =$files['photo']['tmp_name'];
            /*
                $_FILES - is a 2D Associative Array
                    ['photo'] - name of the form input file
                    ['name'] - file name (woman2.jpg)
                    ['tmp_name'] - location of the temporary file
                    ['size'] - size of the file
            */
    

        $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username= '$username' WHERE id =$id "; //photo is separated, using condition

        if($this->conn->query($sql)){
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = "$first_name $last_name";

            // if there is a photo, save it to the db and the file to images folder
            if($photo){
                $sql = "UPDATE users SET photo= '$photo' WHERE id =$id"; //saving its name
                $destination = "../assets/images/$photo"; 

                //save the image to db
                if($this->conn->query($sql)){
                    //save the file to images folder
                    if(move_uploaded_file($tmp_photo, $destination)){
                        header('location: ../views/dashboard.php');
                        exit;
                    }else {
                        die ('Error moving the photo ' ); // local, not conn
                    }

                }else{
                    die ('Error saving your photo name ' . $this->conn->error);
                }

            }
                header("location: ../views/dashboard.php");
                exit;

        }else{
            die ('Error updating your account ' . $this->conn->error);
        }


    }

    public function delete() {
        session_start();
        $id =$_SESSION['id'];

        $sql = "DELETE FROM users WHERE id = $id";

        if($this->conn->query($sql)){
            //header("location: ../views/dashboard.php");
            $this->logout();
            exit;
        } else {
            die("Error deleting your account: " . $this->conn->error);
        }

    }

    

}

?>