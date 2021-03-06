<?php

/**
 * BaseHomePage
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $user_role_id
 * @property string $action
 * @property string $enable_class
 * @property integer $priority
 * @property UserRole $UserRole
 * 
 * @method integer  getId()           Returns the current record's "id" value
 * @method integer  getUserRoleId()   Returns the current record's "user_role_id" value
 * @method string   getAction()       Returns the current record's "action" value
 * @method string   getEnableClass()  Returns the current record's "enable_class" value
 * @method integer  getPriority()     Returns the current record's "priority" value
 * @method UserRole getUserRole()     Returns the current record's "UserRole" value
 * @method HomePage setId()           Sets the current record's "id" value
 * @method HomePage setUserRoleId()   Sets the current record's "user_role_id" value
 * @method HomePage setAction()       Sets the current record's "action" value
 * @method HomePage setEnableClass()  Sets the current record's "enable_class" value
 * @method HomePage setPriority()     Sets the current record's "priority" value
 * @method HomePage setUserRole()     Sets the current record's "UserRole" value
 * 
 * @package    orangehrm
 * @subpackage model\core\base
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseHomePage extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('ohrm_home_page');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('user_role_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('action', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        $this->hasColumn('enable_class', 'string', 100, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 100,
             ));
        $this->hasColumn('priority', 'integer', null, array(
             'type' => 'integer',
             'default' => '0',
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('UserRole', array(
             'local' => 'user_role_id',
             'foreign' => 'id'));
    }
}