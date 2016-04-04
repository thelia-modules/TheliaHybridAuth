<?php

namespace TheliaHybridAuth\Model\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use TheliaHybridAuth\Model\ProviderConfig as ChildProviderConfig;
use TheliaHybridAuth\Model\ProviderConfigQuery as ChildProviderConfigQuery;
use TheliaHybridAuth\Model\Map\ProviderConfigTableMap;

/**
 * Base class that represents a query for the 'provider_config' table.
 *
 *
 *
 * @method     ChildProviderConfigQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildProviderConfigQuery orderByProvider($order = Criteria::ASC) Order by the provider column
 * @method     ChildProviderConfigQuery orderByProviderKey($order = Criteria::ASC) Order by the provider_key column
 * @method     ChildProviderConfigQuery orderBySecret($order = Criteria::ASC) Order by the secret column
 * @method     ChildProviderConfigQuery orderByEnabled($order = Criteria::ASC) Order by the enabled column
 * @method     ChildProviderConfigQuery orderByScope($order = Criteria::ASC) Order by the scope column
 *
 * @method     ChildProviderConfigQuery groupById() Group by the id column
 * @method     ChildProviderConfigQuery groupByProvider() Group by the provider column
 * @method     ChildProviderConfigQuery groupByProviderKey() Group by the provider_key column
 * @method     ChildProviderConfigQuery groupBySecret() Group by the secret column
 * @method     ChildProviderConfigQuery groupByEnabled() Group by the enabled column
 * @method     ChildProviderConfigQuery groupByScope() Group by the scope column
 *
 * @method     ChildProviderConfigQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildProviderConfigQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildProviderConfigQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildProviderConfigQuery leftJoinHybridAuth($relationAlias = null) Adds a LEFT JOIN clause to the query using the HybridAuth relation
 * @method     ChildProviderConfigQuery rightJoinHybridAuth($relationAlias = null) Adds a RIGHT JOIN clause to the query using the HybridAuth relation
 * @method     ChildProviderConfigQuery innerJoinHybridAuth($relationAlias = null) Adds a INNER JOIN clause to the query using the HybridAuth relation
 *
 * @method     ChildProviderConfig findOne(ConnectionInterface $con = null) Return the first ChildProviderConfig matching the query
 * @method     ChildProviderConfig findOneOrCreate(ConnectionInterface $con = null) Return the first ChildProviderConfig matching the query, or a new ChildProviderConfig object populated from the query conditions when no match is found
 *
 * @method     ChildProviderConfig findOneById(int $id) Return the first ChildProviderConfig filtered by the id column
 * @method     ChildProviderConfig findOneByProvider(string $provider) Return the first ChildProviderConfig filtered by the provider column
 * @method     ChildProviderConfig findOneByProviderKey(string $provider_key) Return the first ChildProviderConfig filtered by the provider_key column
 * @method     ChildProviderConfig findOneBySecret(string $secret) Return the first ChildProviderConfig filtered by the secret column
 * @method     ChildProviderConfig findOneByEnabled(boolean $enabled) Return the first ChildProviderConfig filtered by the enabled column
 * @method     ChildProviderConfig findOneByScope(string $scope) Return the first ChildProviderConfig filtered by the scope column
 *
 * @method     array findById(int $id) Return ChildProviderConfig objects filtered by the id column
 * @method     array findByProvider(string $provider) Return ChildProviderConfig objects filtered by the provider column
 * @method     array findByProviderKey(string $provider_key) Return ChildProviderConfig objects filtered by the provider_key column
 * @method     array findBySecret(string $secret) Return ChildProviderConfig objects filtered by the secret column
 * @method     array findByEnabled(boolean $enabled) Return ChildProviderConfig objects filtered by the enabled column
 * @method     array findByScope(string $scope) Return ChildProviderConfig objects filtered by the scope column
 *
 */
abstract class ProviderConfigQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \TheliaHybridAuth\Model\Base\ProviderConfigQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\TheliaHybridAuth\\Model\\ProviderConfig', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildProviderConfigQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildProviderConfigQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \TheliaHybridAuth\Model\ProviderConfigQuery) {
            return $criteria;
        }
        $query = new \TheliaHybridAuth\Model\ProviderConfigQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildProviderConfig|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ProviderConfigTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProviderConfigTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildProviderConfig A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PROVIDER, PROVIDER_KEY, SECRET, ENABLED, SCOPE FROM provider_config WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildProviderConfig();
            $obj->hydrate($row);
            ProviderConfigTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildProviderConfig|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ProviderConfigTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ProviderConfigTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ProviderConfigTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ProviderConfigTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProviderConfigTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the provider column
     *
     * Example usage:
     * <code>
     * $query->filterByProvider('fooValue');   // WHERE provider = 'fooValue'
     * $query->filterByProvider('%fooValue%'); // WHERE provider LIKE '%fooValue%'
     * </code>
     *
     * @param     string $provider The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByProvider($provider = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($provider)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $provider)) {
                $provider = str_replace('*', '%', $provider);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProviderConfigTableMap::PROVIDER, $provider, $comparison);
    }

    /**
     * Filter the query on the provider_key column
     *
     * Example usage:
     * <code>
     * $query->filterByProviderKey('fooValue');   // WHERE provider_key = 'fooValue'
     * $query->filterByProviderKey('%fooValue%'); // WHERE provider_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $providerKey The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByProviderKey($providerKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($providerKey)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $providerKey)) {
                $providerKey = str_replace('*', '%', $providerKey);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProviderConfigTableMap::PROVIDER_KEY, $providerKey, $comparison);
    }

    /**
     * Filter the query on the secret column
     *
     * Example usage:
     * <code>
     * $query->filterBySecret('fooValue');   // WHERE secret = 'fooValue'
     * $query->filterBySecret('%fooValue%'); // WHERE secret LIKE '%fooValue%'
     * </code>
     *
     * @param     string $secret The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterBySecret($secret = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($secret)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $secret)) {
                $secret = str_replace('*', '%', $secret);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProviderConfigTableMap::SECRET, $secret, $comparison);
    }

    /**
     * Filter the query on the enabled column
     *
     * Example usage:
     * <code>
     * $query->filterByEnabled(true); // WHERE enabled = true
     * $query->filterByEnabled('yes'); // WHERE enabled = true
     * </code>
     *
     * @param     boolean|string $enabled The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByEnabled($enabled = null, $comparison = null)
    {
        if (is_string($enabled)) {
            $enabled = in_array(strtolower($enabled), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ProviderConfigTableMap::ENABLED, $enabled, $comparison);
    }

    /**
     * Filter the query on the scope column
     *
     * Example usage:
     * <code>
     * $query->filterByScope('fooValue');   // WHERE scope = 'fooValue'
     * $query->filterByScope('%fooValue%'); // WHERE scope LIKE '%fooValue%'
     * </code>
     *
     * @param     string $scope The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByScope($scope = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($scope)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $scope)) {
                $scope = str_replace('*', '%', $scope);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProviderConfigTableMap::SCOPE, $scope, $comparison);
    }

    /**
     * Filter the query by a related \TheliaHybridAuth\Model\HybridAuth object
     *
     * @param \TheliaHybridAuth\Model\HybridAuth|ObjectCollection $hybridAuth  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function filterByHybridAuth($hybridAuth, $comparison = null)
    {
        if ($hybridAuth instanceof \TheliaHybridAuth\Model\HybridAuth) {
            return $this
                ->addUsingAlias(ProviderConfigTableMap::PROVIDER, $hybridAuth->getProvider(), $comparison);
        } elseif ($hybridAuth instanceof ObjectCollection) {
            return $this
                ->useHybridAuthQuery()
                ->filterByPrimaryKeys($hybridAuth->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByHybridAuth() only accepts arguments of type \TheliaHybridAuth\Model\HybridAuth or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the HybridAuth relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function joinHybridAuth($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('HybridAuth');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'HybridAuth');
        }

        return $this;
    }

    /**
     * Use the HybridAuth relation HybridAuth object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \TheliaHybridAuth\Model\HybridAuthQuery A secondary query class using the current class as primary query
     */
    public function useHybridAuthQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinHybridAuth($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'HybridAuth', '\TheliaHybridAuth\Model\HybridAuthQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildProviderConfig $providerConfig Object to remove from the list of results
     *
     * @return ChildProviderConfigQuery The current query, for fluid interface
     */
    public function prune($providerConfig = null)
    {
        if ($providerConfig) {
            $this->addUsingAlias(ProviderConfigTableMap::ID, $providerConfig->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the provider_config table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProviderConfigTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ProviderConfigTableMap::clearInstancePool();
            ProviderConfigTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildProviderConfig or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildProviderConfig object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProviderConfigTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ProviderConfigTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        ProviderConfigTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ProviderConfigTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // ProviderConfigQuery
