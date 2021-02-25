<?php
namespace Learning\Resource\Mongodb;

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\Session;

class Operator
{
    private Client $client;

    private const CONNECTION_URI_FORMAT = 'mongodb%s://%s%s';
    private const CONNECTION_URI_AUTH_INFO_FORMAT = '%s:%s@';

    public function __construct(array $overwriteUriConfig = [], array $uriOptions = [], array $driverOptions = [])
    {
        $srvFlag = $overwriteUriConfig['srvFlag'] ?? MONGODB_CONNECTION_SRV_FLAG;
        $host = $overwriteUriConfig['host'] ?? MONGODB_CONNECTION_HOST;
        $username = $overwriteUriConfig['username'] ?? MONGODB_CONNECTION_USERNAME;
        $password = $overwriteUriConfig['password'] ?? MONGODB_CONNECTION_PASSWORD;

        $existsAuthInfo = true;
        if (empty($username) || empty($password)) {
            $existsAuthInfo = false;
        }

        $connectionUri = sprintf(
            self::CONNECTION_URI_FORMAT,
            $srvFlag ? '+srv' : '',
            $existsAuthInfo ? sprintf(self::CONNECTION_URI_AUTH_INFO_FORMAT, $username, $password) : '',
            $host
        );

        $this->client = new Client($connectionUri, $uriOptions, $driverOptions);
    }

    /**
     * get database.
     *
     * @param string $databaseName
     * @param array $options
     * @return Database
     */
    public function getDatabase(string $databaseName, array $options = []): Database
    {
        return $this->client->selectDatabase($databaseName, $options);
    }

    /**
     * start session.
     *
     * @param array $options
     * @return Session
     */
    public function startSession(array $options = []): Session
    {
        return $this->client->startSession();
    }
}