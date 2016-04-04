<?php

namespace TheliaHybridAuth\Model\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use TheliaHybridAuth\Model\HybridAuth as ChildHybridAuth;
use TheliaHybridAuth\Model\HybridAuthQuery as ChildHybridAuthQuery;
use TheliaHybridAuth\Model\ProviderConfig as ChildProviderConfig;
use TheliaHybridAuth\Model\ProviderConfigQuery as ChildProviderConfigQuery;
use TheliaHybridAuth\Model\Map\ProviderConfigTableMap;

abstract class ProviderConfig implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\TheliaHybridAuth\\Model\\Map\\ProviderConfigTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the provider field.
     * @var        string
     */
    protected $provider;

    /**
     * The value for the provider_key field.
     * @var        string
     */
    protected $provider_key;

    /**
     * The value for the secret field.
     * @var        string
     */
    protected $secret;

    /**
     * The value for the enabled field.
     * @var        boolean
     */
    protected $enabled;

    /**
     * The value for the scope field.
     * @var        string
     */
    protected $scope;

    /**
     * @var        ObjectCollection|ChildHybridAuth[] Collection to store aggregation of ChildHybridAuth objects.
     */
    protected $collHybridAuths;
    protected $collHybridAuthsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $hybridAuthsScheduledForDeletion = null;

    /**
     * Initializes internal state of TheliaHybridAuth\Model\Base\ProviderConfig object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>ProviderConfig</code> instance.  If
     * <code>obj</code> is an instance of <code>ProviderConfig</code>, delegates to
     * <code>equals(ProviderConfig)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return ProviderConfig The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return ProviderConfig The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [provider] column value.
     *
     * @return   string
     */
    public function getProvider()
    {

        return $this->provider;
    }

    /**
     * Get the [provider_key] column value.
     *
     * @return   string
     */
    public function getProviderKey()
    {

        return $this->provider_key;
    }

    /**
     * Get the [secret] column value.
     *
     * @return   string
     */
    public function getSecret()
    {

        return $this->secret;
    }

    /**
     * Get the [enabled] column value.
     *
     * @return   boolean
     */
    public function getEnabled()
    {

        return $this->enabled;
    }

    /**
     * Get the [scope] column value.
     *
     * @return   string
     */
    public function getScope()
    {

        return $this->scope;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ProviderConfigTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [provider] column.
     *
     * @param      string $v new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setProvider($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->provider !== $v) {
            $this->provider = $v;
            $this->modifiedColumns[ProviderConfigTableMap::PROVIDER] = true;
        }


        return $this;
    } // setProvider()

    /**
     * Set the value of [provider_key] column.
     *
     * @param      string $v new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setProviderKey($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->provider_key !== $v) {
            $this->provider_key = $v;
            $this->modifiedColumns[ProviderConfigTableMap::PROVIDER_KEY] = true;
        }


        return $this;
    } // setProviderKey()

    /**
     * Set the value of [secret] column.
     *
     * @param      string $v new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setSecret($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->secret !== $v) {
            $this->secret = $v;
            $this->modifiedColumns[ProviderConfigTableMap::SECRET] = true;
        }


        return $this;
    } // setSecret()

    /**
     * Sets the value of the [enabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param      boolean|integer|string $v The new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setEnabled($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->enabled !== $v) {
            $this->enabled = $v;
            $this->modifiedColumns[ProviderConfigTableMap::ENABLED] = true;
        }


        return $this;
    } // setEnabled()

    /**
     * Set the value of [scope] column.
     *
     * @param      string $v new value
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function setScope($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->scope !== $v) {
            $this->scope = $v;
            $this->modifiedColumns[ProviderConfigTableMap::SCOPE] = true;
        }


        return $this;
    } // setScope()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ProviderConfigTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ProviderConfigTableMap::translateFieldName('Provider', TableMap::TYPE_PHPNAME, $indexType)];
            $this->provider = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ProviderConfigTableMap::translateFieldName('ProviderKey', TableMap::TYPE_PHPNAME, $indexType)];
            $this->provider_key = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ProviderConfigTableMap::translateFieldName('Secret', TableMap::TYPE_PHPNAME, $indexType)];
            $this->secret = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ProviderConfigTableMap::translateFieldName('Enabled', TableMap::TYPE_PHPNAME, $indexType)];
            $this->enabled = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ProviderConfigTableMap::translateFieldName('Scope', TableMap::TYPE_PHPNAME, $indexType)];
            $this->scope = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = ProviderConfigTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \TheliaHybridAuth\Model\ProviderConfig object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProviderConfigTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildProviderConfigQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collHybridAuths = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see ProviderConfig::setDeleted()
     * @see ProviderConfig::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProviderConfigTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildProviderConfigQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProviderConfigTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ProviderConfigTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->hybridAuthsScheduledForDeletion !== null) {
                if (!$this->hybridAuthsScheduledForDeletion->isEmpty()) {
                    \TheliaHybridAuth\Model\HybridAuthQuery::create()
                        ->filterByPrimaryKeys($this->hybridAuthsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->hybridAuthsScheduledForDeletion = null;
                }
            }

                if ($this->collHybridAuths !== null) {
            foreach ($this->collHybridAuths as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[ProviderConfigTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ProviderConfigTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ProviderConfigTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(ProviderConfigTableMap::PROVIDER)) {
            $modifiedColumns[':p' . $index++]  = 'PROVIDER';
        }
        if ($this->isColumnModified(ProviderConfigTableMap::PROVIDER_KEY)) {
            $modifiedColumns[':p' . $index++]  = 'PROVIDER_KEY';
        }
        if ($this->isColumnModified(ProviderConfigTableMap::SECRET)) {
            $modifiedColumns[':p' . $index++]  = 'SECRET';
        }
        if ($this->isColumnModified(ProviderConfigTableMap::ENABLED)) {
            $modifiedColumns[':p' . $index++]  = 'ENABLED';
        }
        if ($this->isColumnModified(ProviderConfigTableMap::SCOPE)) {
            $modifiedColumns[':p' . $index++]  = 'SCOPE';
        }

        $sql = sprintf(
            'INSERT INTO provider_config (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'PROVIDER':
                        $stmt->bindValue($identifier, $this->provider, PDO::PARAM_STR);
                        break;
                    case 'PROVIDER_KEY':
                        $stmt->bindValue($identifier, $this->provider_key, PDO::PARAM_STR);
                        break;
                    case 'SECRET':
                        $stmt->bindValue($identifier, $this->secret, PDO::PARAM_STR);
                        break;
                    case 'ENABLED':
                        $stmt->bindValue($identifier, (int) $this->enabled, PDO::PARAM_INT);
                        break;
                    case 'SCOPE':
                        $stmt->bindValue($identifier, $this->scope, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ProviderConfigTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getProvider();
                break;
            case 2:
                return $this->getProviderKey();
                break;
            case 3:
                return $this->getSecret();
                break;
            case 4:
                return $this->getEnabled();
                break;
            case 5:
                return $this->getScope();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['ProviderConfig'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['ProviderConfig'][$this->getPrimaryKey()] = true;
        $keys = ProviderConfigTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getProvider(),
            $keys[2] => $this->getProviderKey(),
            $keys[3] => $this->getSecret(),
            $keys[4] => $this->getEnabled(),
            $keys[5] => $this->getScope(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collHybridAuths) {
                $result['HybridAuths'] = $this->collHybridAuths->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ProviderConfigTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setProvider($value);
                break;
            case 2:
                $this->setProviderKey($value);
                break;
            case 3:
                $this->setSecret($value);
                break;
            case 4:
                $this->setEnabled($value);
                break;
            case 5:
                $this->setScope($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = ProviderConfigTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setProvider($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setProviderKey($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setSecret($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setEnabled($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setScope($arr[$keys[5]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ProviderConfigTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ProviderConfigTableMap::ID)) $criteria->add(ProviderConfigTableMap::ID, $this->id);
        if ($this->isColumnModified(ProviderConfigTableMap::PROVIDER)) $criteria->add(ProviderConfigTableMap::PROVIDER, $this->provider);
        if ($this->isColumnModified(ProviderConfigTableMap::PROVIDER_KEY)) $criteria->add(ProviderConfigTableMap::PROVIDER_KEY, $this->provider_key);
        if ($this->isColumnModified(ProviderConfigTableMap::SECRET)) $criteria->add(ProviderConfigTableMap::SECRET, $this->secret);
        if ($this->isColumnModified(ProviderConfigTableMap::ENABLED)) $criteria->add(ProviderConfigTableMap::ENABLED, $this->enabled);
        if ($this->isColumnModified(ProviderConfigTableMap::SCOPE)) $criteria->add(ProviderConfigTableMap::SCOPE, $this->scope);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(ProviderConfigTableMap::DATABASE_NAME);
        $criteria->add(ProviderConfigTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \TheliaHybridAuth\Model\ProviderConfig (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setProvider($this->getProvider());
        $copyObj->setProviderKey($this->getProviderKey());
        $copyObj->setSecret($this->getSecret());
        $copyObj->setEnabled($this->getEnabled());
        $copyObj->setScope($this->getScope());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getHybridAuths() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addHybridAuth($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \TheliaHybridAuth\Model\ProviderConfig Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('HybridAuth' == $relationName) {
            return $this->initHybridAuths();
        }
    }

    /**
     * Clears out the collHybridAuths collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addHybridAuths()
     */
    public function clearHybridAuths()
    {
        $this->collHybridAuths = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collHybridAuths collection loaded partially.
     */
    public function resetPartialHybridAuths($v = true)
    {
        $this->collHybridAuthsPartial = $v;
    }

    /**
     * Initializes the collHybridAuths collection.
     *
     * By default this just sets the collHybridAuths collection to an empty array (like clearcollHybridAuths());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initHybridAuths($overrideExisting = true)
    {
        if (null !== $this->collHybridAuths && !$overrideExisting) {
            return;
        }
        $this->collHybridAuths = new ObjectCollection();
        $this->collHybridAuths->setModel('\TheliaHybridAuth\Model\HybridAuth');
    }

    /**
     * Gets an array of ChildHybridAuth objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildProviderConfig is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildHybridAuth[] List of ChildHybridAuth objects
     * @throws PropelException
     */
    public function getHybridAuths($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collHybridAuthsPartial && !$this->isNew();
        if (null === $this->collHybridAuths || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collHybridAuths) {
                // return empty collection
                $this->initHybridAuths();
            } else {
                $collHybridAuths = ChildHybridAuthQuery::create(null, $criteria)
                    ->filterByProviderConfig($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collHybridAuthsPartial && count($collHybridAuths)) {
                        $this->initHybridAuths(false);

                        foreach ($collHybridAuths as $obj) {
                            if (false == $this->collHybridAuths->contains($obj)) {
                                $this->collHybridAuths->append($obj);
                            }
                        }

                        $this->collHybridAuthsPartial = true;
                    }

                    reset($collHybridAuths);

                    return $collHybridAuths;
                }

                if ($partial && $this->collHybridAuths) {
                    foreach ($this->collHybridAuths as $obj) {
                        if ($obj->isNew()) {
                            $collHybridAuths[] = $obj;
                        }
                    }
                }

                $this->collHybridAuths = $collHybridAuths;
                $this->collHybridAuthsPartial = false;
            }
        }

        return $this->collHybridAuths;
    }

    /**
     * Sets a collection of HybridAuth objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $hybridAuths A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildProviderConfig The current object (for fluent API support)
     */
    public function setHybridAuths(Collection $hybridAuths, ConnectionInterface $con = null)
    {
        $hybridAuthsToDelete = $this->getHybridAuths(new Criteria(), $con)->diff($hybridAuths);


        $this->hybridAuthsScheduledForDeletion = $hybridAuthsToDelete;

        foreach ($hybridAuthsToDelete as $hybridAuthRemoved) {
            $hybridAuthRemoved->setProviderConfig(null);
        }

        $this->collHybridAuths = null;
        foreach ($hybridAuths as $hybridAuth) {
            $this->addHybridAuth($hybridAuth);
        }

        $this->collHybridAuths = $hybridAuths;
        $this->collHybridAuthsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related HybridAuth objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related HybridAuth objects.
     * @throws PropelException
     */
    public function countHybridAuths(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collHybridAuthsPartial && !$this->isNew();
        if (null === $this->collHybridAuths || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collHybridAuths) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getHybridAuths());
            }

            $query = ChildHybridAuthQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByProviderConfig($this)
                ->count($con);
        }

        return count($this->collHybridAuths);
    }

    /**
     * Method called to associate a ChildHybridAuth object to this object
     * through the ChildHybridAuth foreign key attribute.
     *
     * @param    ChildHybridAuth $l ChildHybridAuth
     * @return   \TheliaHybridAuth\Model\ProviderConfig The current object (for fluent API support)
     */
    public function addHybridAuth(ChildHybridAuth $l)
    {
        if ($this->collHybridAuths === null) {
            $this->initHybridAuths();
            $this->collHybridAuthsPartial = true;
        }

        if (!in_array($l, $this->collHybridAuths->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddHybridAuth($l);
        }

        return $this;
    }

    /**
     * @param HybridAuth $hybridAuth The hybridAuth object to add.
     */
    protected function doAddHybridAuth($hybridAuth)
    {
        $this->collHybridAuths[]= $hybridAuth;
        $hybridAuth->setProviderConfig($this);
    }

    /**
     * @param  HybridAuth $hybridAuth The hybridAuth object to remove.
     * @return ChildProviderConfig The current object (for fluent API support)
     */
    public function removeHybridAuth($hybridAuth)
    {
        if ($this->getHybridAuths()->contains($hybridAuth)) {
            $this->collHybridAuths->remove($this->collHybridAuths->search($hybridAuth));
            if (null === $this->hybridAuthsScheduledForDeletion) {
                $this->hybridAuthsScheduledForDeletion = clone $this->collHybridAuths;
                $this->hybridAuthsScheduledForDeletion->clear();
            }
            $this->hybridAuthsScheduledForDeletion[]= clone $hybridAuth;
            $hybridAuth->setProviderConfig(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ProviderConfig is new, it will return
     * an empty collection; or if this ProviderConfig has previously
     * been saved, it will retrieve related HybridAuths from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ProviderConfig.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildHybridAuth[] List of ChildHybridAuth objects
     */
    public function getHybridAuthsJoinCustomer($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildHybridAuthQuery::create(null, $criteria);
        $query->joinWith('Customer', $joinBehavior);

        return $this->getHybridAuths($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->provider = null;
        $this->provider_key = null;
        $this->secret = null;
        $this->enabled = null;
        $this->scope = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collHybridAuths) {
                foreach ($this->collHybridAuths as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collHybridAuths = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ProviderConfigTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
