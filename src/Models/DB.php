<?php

namespace App\Models;

use PDO;

use PDOException;

class Booking
{
    public $bookingID;
    public $roomID;
    public $userID;
    public $bookingStatus;
}

class Room
{
    public $roomID;
    public $roomNumber;
    public $roomType;
    public $roomStatus;
}

class User
{
    public $userID;
    public $name;
    public $email;
    public $passwordHash;
    public $role;
    public $photo;
}

class DbStatus
{
    public $status;
    public $error;
    public $lastinsertid;
}

function hashPassword($password)
{

    $cost = 10;

    $options = [
        'cost' => $cost,
    ];

    $passwordhash =  password_hash($password, PASSWORD_BCRYPT, $options);
    return $passwordhash;
}

class DB
{
    protected $dbhost;
    protected $dbuser;
    protected $dbpass;
    protected $dbname;
    protected $db;

    public function __construct($dbhost, $dbuser, $dbpass, $dbname)
    {

        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;

        $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
        $this->db = $db;
    }

    public function close()
    {

        try {
            $this->db = null;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
        }
    }


    // ************************************************************
    // *            Authentication and Authorization              *
    // ************************************************************
    //authentication and authorization start

    // register a new user
    public function insertUser($name, $email, $password, $role, $photo)
    {

        // $role = "member";

        //hash the password using one way md5 brcrypt hashing
        $passwordhash = hashPassword($password);

        try {

            $sql = "INSERT INTO user (name, email, password, role, photo) 
                    VALUES (:name, :email, :password, :role, :photo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("name", $name);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("password", $passwordhash);
            $stmt->bindParam("role", $role);
            $stmt->bindParam("photo", $photo);

            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    // login a user
    public function getUserViaLogin($email)
    {

        $sql = "SELECT userID, name, email, password as passwordhash, role, photo
                FROM user
                WHERE email = :email";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("email", $email);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $user = null;

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new User();
                $user->userID = intval($row['userID']);
                $user->name = $row['name'];
                $user->email = $row['email'];
                $user->passwordHash = $row['passwordhash'];
                $user->role = $row['role'];
                $user->photo = $row['photo'];
            }
        }

        return $user;
    }

    //authentication and authorization done



    // ************************************************************
    // *                            User                          *
    // ************************************************************

    //Get all user
    public function getAllUser()
    {

        $sql = "SELECT * FROM user";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $data = array();

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new User();
                $user->userID = intval($row['userID']);
                $user->name = $row['name'];
                $user->email = $row['email'];
                $user->passwordHash = $row['password'];
                $user->role = $row['role'];
                $user->photo = $row['photo'];

                array_push($data, $user);
            }
        }

        return $data;
    }

    //Create a new user
    //use the same "insertUser()" function above (line 89) in authentication and authorization section

    //Get a user
    public function getUserViaId($id)
    {

        $sql = "SELECT *
                FROM user
                WHERE userID = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $user = null;

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new User();
                $user->userID = intval($row['userID']);
                $user->name = $row['name'];
                $user->email = $row['email'];
                $user->passwordHash = $row['password'];
                $user->role = $row['role'];
                $user->photo = $row['photo'];
            }
        }

        return $user;
    }

    //Update a user
    public function updateUserViaId($id, $name, $email, $password, $role, $photo)
    {
        $passwordhash = hashPassword($password);

        $sql = "UPDATE user
                SET name = :name,
                    email = :email,
                    password = :password,
                    role = :role,
                    photo = :photo
                WHERE userID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("name", $name);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("password", $passwordhash);
            $stmt->bindParam("role", $role);
            $stmt->bindParam("photo", $photo);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    //Delete a user
    public function deleteUserViaId($id)
    {

        $dbstatus = new DbStatus();

        $sql = "DELETE 
                FROM user 
                WHERE userID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbstatus->status = true;
            $dbstatus->error = "none";
            return $dbstatus;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbstatus->status = false;
            $dbstatus->error = $errorMessage;
            return $dbstatus;
        }
    }



    // ************************************************************
    // *                         Booking                          *
    // ************************************************************

    //Get all Booking
    public function getAllBookings()
    {

        $sql = "SELECT * FROM booking";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $data = array();

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $booking = new Booking();
                $booking->bookingID = intval($row['bookingID']);
                $booking->roomID = intval($row['roomID']);
                $booking->userID = intval($row['userID']);
                $booking->bookingStatus = intval($row['bookingStatus']);

                array_push($data, $booking);
            }
        }

        return $data;
    }

    //Create a new Booking
    public function insertBooking($roomID, $userID, $bookingStatus)
    {

        try {

            $sql = "INSERT INTO booking (roomID, userID, bookingStatus) 
                    VALUES (:roomID, :userID, :bookingStatus)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("roomID", $roomID);
            $stmt->bindParam("userID", $userID);
            $stmt->bindParam("bookingStatus", $bookingStatus);

            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    //Get a Booking
    public function getBookingViaId($id)
    {

        $sql = "SELECT *
                FROM booking
                WHERE bookingID = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $booking = null;

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $booking = new Booking();
                $booking->bookingID = intval($row['bookingID']);
                $booking->roomID = intval($row['roomID']);
                $booking->userID = intval($row['userID']);
                $booking->bookingStatus = intval($row['bookingStatus']);
            }
        }

        return $booking;
    }

    //Update a Booking
    public function updateBookingViaId($id, $roomID, $userID, $bookingStatus)
    {

        $sql = "UPDATE booking
                SET roomID = :roomID,
                    userID = :userID,
                    bookingStatus = :bookingStatus
                WHERE bookingID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("roomID", $roomID);
            $stmt->bindParam("userID", $userID);
            $stmt->bindParam("bookingStatus", $bookingStatus);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    //Delete a Booking
    public function deleteBookingViaId($id)
    {

        $dbstatus = new DbStatus();

        $sql = "DELETE 
                FROM booking 
                WHERE bookingID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbstatus->status = true;
            $dbstatus->error = "none";
            return $dbstatus;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbstatus->status = false;
            $dbstatus->error = $errorMessage;
            return $dbstatus;
        }
    }



    // ************************************************************
    // *                            Room                          *
    // ************************************************************

    //Get all Room
    public function getAllRooms()
    {

        $sql = "SELECT * FROM room";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $data = array();

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $room = new Room();
                $room->roomID = intval($row['roomID']);
                $room->roomNumber = intval($row['roomNumber']);
                $room->roomType = $row['roomType'];
                $room->roomStatus = intval($row['roomStatus']);

                array_push($data, $room);
            }
        }

        return $data;
    }

    //Create a new Room
    public function insertRoom($roomNumber, $roomType, $roomStatus)
    {

        try {

            $sql = "INSERT INTO room (roomNumber, roomType, roomStatus) 
                    VALUES (:roomNumber, :roomType, :roomStatus)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("roomNumber", $roomNumber);
            $stmt->bindParam("roomType", $roomType);
            $stmt->bindParam("roomStatus", $roomStatus);

            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    //Get a Room
    public function getRoomViaId($id)
    {

        $sql = "SELECT *
                FROM room
                WHERE roomID = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        $room = null;

        if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $room = new Room();
                $room->roomID = intval($row['roomID']);
                $room->roomNumber = intval($row['roomNumber']);
                $room->roomType = $row['roomType'];
                $room->roomStatus = intval($row['roomStatus']);
            }
        }

        return $room;
    }

    //Update a Room
    public function updateRoomViaId($id, $roomNumber, $roomType, $roomStatus)
    {

        $sql = "UPDATE room
                SET roomNumber = :roomNumber,
                    roomType = :roomType,
                    roomStatus = :roomStatus
                WHERE roomID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("roomNumber", $roomNumber);
            $stmt->bindParam("roomType", $roomType);
            $stmt->bindParam("roomStatus", $roomStatus);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";

            return $dbs;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
        }
    }

    //Delete a Room
    public function deleteRoomViaId($id)
    {

        $dbstatus = new DbStatus();

        $sql = "DELETE 
                FROM room 
                WHERE roomID = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();

            $dbstatus->status = true;
            $dbstatus->error = "none";
            return $dbstatus;
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbstatus->status = false;
            $dbstatus->error = $errorMessage;
            return $dbstatus;
        }
    }
}
