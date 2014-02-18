<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    if ($this->local_id) {
        
				GenerateSecret($this->uid);
				InitUser($this->uid);
				SetAttribute("user", "LastActivity", date('U'), $this->local_id);
				ProcessTriggers("user_login", false, false, false, false);
				ThingsToDoAtLogin();
        setcookie('session',$_COOKIE['session'],0,'/');
        
        return true;
    } else {
        return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      $user_data = $this->buildLocalUser();
      $sql = sprintf("INSERT INTO CRMloginusers(name,password,FULLNAME,EMAIL,FORCEPASSCHANGE,LASTPASSCHANGE,administrator) VALUES('%s',PASSWORD('%s'),'%s','%s','%s','%s','%s')",
        mysql_real_escape_string($user_data['name']),
        mysql_real_escape_string($user_data['password']),
        mysql_real_escape_string($user_data['fullname']),
        mysql_real_escape_string($user_data['email']),
        mysql_real_escape_string($user_data['forcepasswordchange']),
        mysql_real_escape_string($user_data['lastpasschange']),
        mysql_real_escape_string($user_data['administrator'])
      );
			$q = mysql_query($sql,$this->connection);
      
      if ($q) {
        $lid = mysql_insert_id();
      }
    }
    
    return $lid;
  }
  
  /**
   * Build the local user for creation
   *
   * @return the ID of the user created, null otherwise
   */
  protected function buildLocalUser()
  {
    $user_data = array(
      'name' => $this->uid,
      'fullname' => "$this->name $this->surname",
      'password' => $this->generatePassword(),
      'email' => $this->email,
      'administrator' => $this->getRoleIdToAssign(),
      'forcepasswordchange' => 'n',
      'lastpasschange' => date('Y-m-d H:i:s')
    );
    
    
    
    return $user_data;
  }
  
  /**
   * Return the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return wether the user is administrator or not
   */
  public function getRoleIdToAssign() {
    $role_id = 'no'; // User
    
    if ($this->app_owner) {
      $role_id = 'yes'; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role_id = 'yes';
        } else {
          $role_id = 'no';
        }
      }
    }
    
    return $role_id;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $sql = sprintf("SELECT id FROM CRMloginusers WHERE mno_uid = '%s'", mysql_real_escape_string($this->uid));
    $q = mysql_query($sql,$this->connection);
    
    if ($q) {
      $result = mysql_fetch_assoc($q);
      
      if ($result && $result['id']) {
        return $result['id'];
      }
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {    
    $sql = sprintf("SELECT id FROM CRMloginusers WHERE EMAIL = '%s'", mysql_real_escape_string($this->email));
    $q = mysql_query($sql,$this->connection);
    
    if ($q) {
      $result = mysql_fetch_assoc($q);
      
      if ($result && $result['id']) {
        return $result['id'];
      }
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       
       $sql = sprintf("UPDATE CRMloginusers SET name = '%s',
        FULLNAME = '%s',
        EMAIL = '%s'
        WHERE id = %s", 
        mysql_real_escape_string($this->uid), 
        mysql_real_escape_string("$this->name $this->surname"), 
        mysql_real_escape_string($this->email),
        mysql_real_escape_string($this->local_id));
        
       $upd = mysql_query($sql,$this->connection);
       
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $sql = sprintf("UPDATE CRMloginusers SET mno_uid = '%s' WHERE id = %s", 
       mysql_real_escape_string($this->uid),
       mysql_real_escape_string($this->local_id));
      
      $upd = mysql_query($sql,$this->connection);
      return $upd;
    }
    
    return false;
  }
}