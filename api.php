<?php

class ConnectionString
{

    /**
     * @var String
     */
    private $serverName;
    /**
     * @var String
     */
    private $userName;
    /**
     * @var String
     */
    private $password;
    /**
     * @var String
     */
    private $database;

    /**
     * @return String
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param String $serverName
     * @return ConnectionString
     */
    public function setServerName(string $serverName): ConnectionString
    {
        $this->serverName = $serverName;
        return $this;
    }

    /**
     * @return String
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param String $userName
     * @return ConnectionString
     */
    public function setUserName(string $userName): ConnectionString
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return String
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param String $password
     * @return ConnectionString
     */
    public function setPassword(string $password): ConnectionString
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return String
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param String $database
     * @return ConnectionString
     */
    public function setDatabase(string $database): ConnectionString
    {
        $this->database = $database;
        return $this;
    }


}

class PersonModel
{
    /**
     * @var int
     */
    private $personId;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $age;


    /**
     * @return int
     */
    public function getPersonId(): int
    {
        return $this->personId;
    }

    /**
     * @param int $personId
     * @return personModel
     */
    public function setPersonId(int $personId): personModel
    {
        $this->personId = $personId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return personModel
     */
    public function setName(string $name): personModel
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     * @return personModel
     */
    public function setAge(int $age): personModel
    {
        $this->age = $age;
        return $this;
    }

}

//

/**
 * most common people will use enum for static value . php can be use interface or class
 * the point here to store one place all the string  value
 */
interface  ReturnCode
{
    // const ACCESS_GRANTED = "200";

    const CONNECTION_ERROR = "001";

    const ACCESS_DENIED_NO_MODE = "Cannot identify mode ";

    const ACCESS_DENIED = 500;

    const CREATE_SUCCESS = 101;

    const READ_SUCCESS = 201;

    const UPDATE_SUCCESS = 301;

    const DELETE_SUCCESS = 401;

    const QUERY_FAILURE = 601;
}

class SimpleOopProper
{

    /**
     * @var ConnectionString
     */
    private $connectionString;
    /**
     * @var PersonModel
     */
    private $model;
    /**
     * @var mysqli
     */
    private $connection;

    private $searchTextValue;

    /**
     * SimpleOop constructor.
     * @throws Exception
     */
    function __construct()
    {
        // declare object / injection
        $this->model = new PersonModel();
        $this->connectionString = new ConnectionString();

        // connection to the database
        try {
            $this->connect();
        } catch (Exception $exception) {
            // the more proper is to send exception to a file list / or table and send to end user .System fail to access
            throw new Exception($exception->getMessage(), ReturnCode::CONNECTION_ERROR);
        }
        // all parameter value at here and bind to the model the value
        $this->setParameter();
    }

    /**
     * Connection to the database
     * @throws Exception
     */
    function connect()
    {
        // init value , this may diff with your setup. the proper is to create a file outside from the www folder so outsider
        // cannot get access to the file .
        $this->connectionString->setServerName("localhost");
        $this->connectionString->setUserName("youtuber");
        $this->connectionString->setPassword("123456");
        $this->connectionString->setDatabase("youtuber");
        try {
            $this->connection = new mysqli($this->connectionString->getServerName(), $this->connectionString->getUserName(), $this->connectionString->getPassword(), $this->connectionString->getDatabase());
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    /**
     * Binding Web Parameter to model
     */
    function setParameter()
    {
        $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
        $age = filter_input(INPUT_POST, "age", FILTER_SANITIZE_NUMBER_INT);
        $personId = filter_input(INPUT_POST, "personId", FILTER_SANITIZE_NUMBER_INT);
        $search = filter_input(INPUT_POST, "search", FILTER_SANITIZE_STRING);

        if ($name && strlen($name) > 0) {
            $this->model->setName($name);
        }
        if ($search && strlen($search) > 0) {
            $this->setSearchTextValue($search);
        }
        if ($age && is_numeric($age)) {
            if ($age > 0) {
                $this->model->setAge($age);
            }
        }
        if ($personId && is_numeric($personId)) {
            if ($personId > 0) {
                $this->model->setPersonId($personId);
            }
        }
    }

    /**
     * @throws Exception
     */
    function create()
    {
        $this->connection->autocommit(false);

        // bind parameter required parameter not object value kinda weird
        $var1 = $this->model->getName();
        $var2 = $this->model->getAge();

        if (strlen($var1) > 0 && $var2 > 0) {
            /// but somebody still scares if the value is not correct or idiom sql injection
            $statement = $this->connection->prepare("INSERT INTO person VALUES (null,?, ?)");
            // s -> string, i -> integer , d -  double , b - blob
            $statement->bind_param("si", $var1, $var2);
            try {
                $statement->execute();
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage(), ReturnCode::QUERY_FAILURE);
            }

            $this->connection->commit();
            echo json_encode(
                [
                    "status" => true,
                    "code" => ReturnCode::CREATE_SUCCESS,
                    "lastInsertId"=> $statement->insert_id
                ]
            );

        } else {
            throw new Exception(ReturnCode::ACCESS_DENIED);
        }
    }

    /**
     * @throws Exception
     */
    function read()
    {
        // you don't need to commit work here ya !
        try {
            $result = $this->connection->query("SELECT * FROM person ORDER BY personId DESC ");
            $data = [];
            while (($row = $result->fetch_assoc()) == TRUE) {
                $data[] = $row;
            }
            echo json_encode(
                [
                    "status" => true,
                    "code" => ReturnCode::READ_SUCCESS,
                    "data" => $data
                ]
            );
        } catch (Exception $exception) {
            throw new Exception(ReturnCode::ACCESS_DENIED, ReturnCode::QUERY_FAILURE);
        }
    }
    /**
     * @throws Exception
     */
    function search()
    {
        // you don't need to commit work here ya !
        $var1 = $this->getSearchTextValue();
        $var2 = &$var1;
        try {
            $statement = $this->connection->prepare("select * from person where name like CONCAT( '%',?,'%') OR age like CONCAT( '%',?,'%') ORDER BY personId DESC ");
            $statement->bind_param("ss", $var1,$var2);
            try {
                $statement->execute();
                $result = $statement->get_result();
                $data = [];
                while (($row = $result->fetch_assoc()) == TRUE) {
                    $data[] = $row;
                }
                echo json_encode(
                    [
                        "status" => true,
                        "code" => ReturnCode::READ_SUCCESS,
                        "data" => $data
                    ]
                );
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage(), ReturnCode::QUERY_FAILURE);
            }

        } catch (Exception $exception) {
            throw new Exception(ReturnCode::ACCESS_DENIED, ReturnCode::QUERY_FAILURE);
        }
    }

    /**
     * @return mixed
     */
    public function getSearchTextValue()
    {
        return $this->searchTextValue;
    }

    /**
     * @param mixed $searchTextValue
     * @return SimpleOopProper
     */
    public function setSearchTextValue($searchTextValue): SimpleOopProper
    {
        $this->searchTextValue = $searchTextValue;
        return $this;
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $this->connection->autocommit(false);

        // bind parameter required parameter not object value kinda weird
        $var1 = $this->model->getName();
        $var2 = $this->model->getAge();
        // the more proper is to hash the id  , so hijacker wouldn't know the value , doesn't matter it was md5 with salt or sha1  or
        // random encryption
        $var3 = $this->model->getPersonId();

        if (strlen($var1) > 0 && $var2 > 0 && $var3 > 0) {
            /// but somebody still scares if the value is not correct or idiom sql injection
            /// whatever we must check record existed before updating
            ///
            $statement = $this->connection->prepare("UPDATE person SET name = ?,age = ? WHERE  personId = ?");
            // s -> string, i -> integer , d -  double , b - blob
            $statement->bind_param("sii", $var1, $var2, $var3);
            try {
                $statement->execute();
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage(), ReturnCode::QUERY_FAILURE);
            }

            $this->connection->commit();
            echo json_encode(
                [
                    "status" => true,
                    "code" => ReturnCode::UPDATE_SUCCESS
                ]
            );

        } else {
            throw new Exception(ReturnCode::ACCESS_DENIED);
        }
    }

    /**
     * @throws Exception
     */
    function delete()
    {
        $this->connection->autocommit(false);

        // bind parameter required parameter not object value kinda weird
        $var1 = $this->model->getPersonId();

        if ($var1 > 0) {
            /// but somebody still scares if the value is not correct or idiom sql injection
            /// the proper is not delete the record but flag it

            /// whatever we must check record existed before deleting
            ///
            $statement = $this->connection->prepare("DELETE FROM person WHERE personId = ? ");
            // s -> string, i -> integer , d -  double , b - blob
            $statement->bind_param("i", $var1);
            try {
                $statement->execute();
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage(), ReturnCode::QUERY_FAILURE);
            }

            $this->connection->commit();
            echo json_encode(
                [
                    "status" => true,
                    "code" => ReturnCode::DELETE_SUCCESS
                ]
            );

        } else {
            throw new Exception(ReturnCode::ACCESS_DENIED);
        }
    }
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // this is to prevent from javascript given cors error

$mode = filter_input(INPUT_POST, "mode", FILTER_SANITIZE_STRING);

$mode_get = filter_input(INPUT_GET, "mode_get", FILTER_SANITIZE_STRING);

$simpleOopProper = new SimpleOopProper();
if($mode_get) {
    try {
        switch ($mode_get) {
            case  "read":
                $simpleOopProper->read();
                break;
        }
    } catch (Exception $exception) {
        echo json_encode([
            "status" => false,
            "message" => "lol".$exception->getMessage(),
            "code" => $exception->getCode()
        ]);
    }
}else {
    try {
        switch ($mode) {
            case  "create":
                $simpleOopProper->create();
                break;
            case  "read":
                $simpleOopProper->read();
                break;
            case  "search":
                $simpleOopProper->search();
                break;
            case  "update":
                $simpleOopProper->update();
                break;
            case  "delete":
                $simpleOopProper->delete();
                break;
            default:
                throw new Exception(ReturnCode::ACCESS_DENIED_NO_MODE, ReturnCode::ACCESS_DENIED);
        }
    } catch (Exception $exception) {
        echo json_encode([
            "status" => false,
            "message" => "post".$exception->getMessage(),
            "code" => $exception->getCode()
        ]);
    }
}