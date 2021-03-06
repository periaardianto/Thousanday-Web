<?php

class Pet {

  private $db;
    
  public function __construct($db) {
    $this->db = $db;
  }

  //read data for one pet
  //index pet_id
  public function readOnePet($id) {
    $petQuery = 'SELECT * FROM pet WHERE pet_id = :id';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $petStmt->execute();
    return $petStmt->fetch(PDO::FETCH_ASSOC);
  }

  //read one pet's skills info
  //index pet_id
  public function readPetSkills($id) {
    $petQuery = 'SELECT skill0_index, skill1_index, skill2_index, skill3_index FROM pet WHERE pet_id = :id';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $petStmt->execute();
    return $petStmt->fetch(PDO::FETCH_ASSOC);
  }
  
  //update pet's skill
  //index pet_id
  public function updatePetSkill($index, $skill, $id) {
    $petQuery = 'UPDATE pet SET skill' . $index . '_index = :skill WHERE pet_id = :id';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $petStmt->bindValue(':skill', $skill, PDO::PARAM_STR);
    $petStmt->execute();
    return $petStmt->rowCount();
  }

  //return owner id and relative id of one pet
  //index pet_id
  public function readPetFamily($id) {
    $familyQuery = 'SELECT owner_id, relative_id, last_update, pet_type, pet_nature FROM pet WHERE pet_id = :id';
    $familyStmt = $this->db->prepare($familyQuery);
    $familyStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $familyStmt->execute();
    return $familyStmt->fetch(PDO::FETCH_ASSOC);
  }
  
  //update pet's name
  //index pet_id
  public function updatePetName($id, $name) {
    $petQuery = 'UPDATE pet SET pet_name = :name WHERE pet_id = :id';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $petStmt->bindValue(':name', $name, PDO::PARAM_STR);
    $petStmt->execute();
    return $petStmt->rowCount();
  }

  //end relative of one pet
  //index pet_id
  public function endPetRelation($id) {
    $petQuery = 'UPDATE pet SET relative_id = NULL WHERE pet_id = :id';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $petStmt->execute();
    return $petStmt->rowCount();
  }
  
  //pet gain ability from upload moment daily
  //index pet_id
  public function updatePetAbility($pet, $time, $checker) {
    $code = mt_rand(0, 4);
    switch ($code) {
      case 0:
        $ability = 'attack';
        break;
      case 1:
        $ability = 'defend';
        break;
      case 2:
        $ability = 'health';
        break;
      case 3:
        $ability = 'swift';
        break;
      case 4:
        $ability = 'lucky';
        break;
    }
    $petQuery = 'UPDATE pet SET ' . $checker . ' = :time, ' . $ability . ' = ' . $ability . 
                ' + 1 WHERE pet_id = :pet';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':time', $time);
    $petStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
    $petStmt->execute();
    return $code;
  }
  
  //add relative for pet
  //index pet_id
  public function addPetRelative($pet, $user) {
    $petQuery = 'UPDATE pet SET relative_id = :relative 
                 WHERE pet_id = :pet AND relative_id is NULL';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':relative', $user, PDO::PARAM_INT);
    $petStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
    $petStmt->execute();
    return $petStmt->rowCount();
  }
  
  //transfer ownership for one pet
  //index pet_id
  public function transferPetOwner($pet, $owner, $relative) {
    $petQuery = 'UPDATE pet SET owner_id = :owner, relative_id = :relative WHERE pet_id = :pet';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->bindValue(':owner', $relative, PDO::PARAM_INT);
    $petStmt->bindValue(':relative', $owner, PDO::PARAM_INT);
    $petStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
    $petStmt->execute();
    return $petStmt->rowCount();
  }
  
  //find pets list fit in certain type and nature
  //index (pet_type, pet_nature)
  public function readFilterPets($type, $nature) {
    $userQuery = 'SELECT pet_id FROM pet WHERE pet_type = :type AND pet_nature = :nature';
    $userStmt = $this->db->prepare($userQuery);
    $userStmt->bindValue(':type', $type, PDO::PARAM_INT);
    $userStmt->bindValue(':nature', $nature, PDO::PARAM_INT);
    $userStmt->execute();
    return $userStmt->fetchAll(PDO::FETCH_NUM);
  }
  
  //get all pets info belong to one user except current one
  //index owner_id
  public function readUserPets($owner, $pet) {
    $userQuery = 'SELECT pet_id, pet_name FROM pet WHERE owner_id = :owner AND pet_id != :pet';
    $userStmt = $this->db->prepare($userQuery);
    $userStmt->bindValue(':owner', $owner, PDO::PARAM_INT);
    $userStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
    $userStmt->execute();
    return $userStmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  //return all pets id belong to one user
  //index owner_id, relative_id
  public function readUserBelong($user) {
    $userQuery = 'SELECT pet_id, pet_name, pet_gender, pet_type, owner_id, relative_id FROM pet
                  WHERE owner_id = :user OR relative_id = :user';
    $userStmt = $this->db->prepare($userQuery);
    $userStmt->bindValue(':user', $user, PDO::PARAM_INT);
    $userStmt->execute();
    return $userStmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  //get all pets belong to owner or relative, except current pet
  //index owner_id, relative_id
  public function readPetFriends($owner, $relative, $pet) {
    $familyQuery = 'SELECT pet_id, pet_name FROM pet FORCE INDEX (owner_id, relative_id) WHERE
                    (owner_id = :owner OR relative_id = :owner OR owner_id = :relative 
                    OR relative_id = :owner) AND pet_id != :pet';
    $familyStmt = $this->db->prepare($familyQuery);
    $familyStmt->bindValue(':owner', $owner, PDO::PARAM_INT);
    $familyStmt->bindValue(':relative', $relative, PDO::PARAM_INT);
    $familyStmt->bindValue(':pet', $pet, PDO::PARAM_INT);
    $familyStmt->execute();
    return $familyStmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  //return pets' name based on a list of pets' id
  //index pet_id
  //is it a good practise to use IN here?
  public function readPetsNames($list) {
    $values = implode(',', $list);
    $petQuery = 'SELECT pet_id, pet_name FROM pet WHERE pet_id IN (' . $values . ')';
    $petStmt = $this->db->prepare($petQuery);
    $petStmt->execute();
    return $petStmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  //create a new pet
  //return pet id
  public function createNewPet($name, $gender, $type, $nature, $user) {
    $time = date('Y-m-d H:i:s');
    $addQuery = 'INSERT INTO pet (pet_name, pet_gender, pet_type, pet_nature, pet_reg, owner_id) 
                 VALUES (:name, :gender, :type, :nature, :reg, :user)';
    $addStmt = $this->db->prepare($addQuery);
    $addStmt->bindValue(':name', $name, PDO::PARAM_STR);
    $addStmt->bindValue(':gender', $gender, PDO::PARAM_INT);
    $addStmt->bindValue(':type', $type, PDO::PARAM_INT);
    $addStmt->bindValue(':nature', $nature, PDO::PARAM_INT);
    $addStmt->bindValue(':reg', $time);
    $addStmt->bindValue(':user', $user, PDO::PARAM_INT);
    $addStmt->execute();
    return $this->db->lastInsertId();
  }

}