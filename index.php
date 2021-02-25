<?php
date_default_timezone_set('Asia/Tokyo');
ini_set('display_errors', 'On');
require('vendor/autoload.php');
require('config/constant.php');

use \Learning\Util\Logger;

Logger::output('start.');

$mongodb = new \Learning\Resource\Mongodb\Operator();
$sampleDb = $mongodb->getDatabase('sample');

$groupId = 'g1';
$executionCount = 0;
do {

    /*
     * retry
     */

    if ($executionCount !== 0 && MAX_RETRY_COUNT >= $executionCount) {
        $delayMicroSec = mt_rand(1, 3000000*$executionCount);
        Logger::output('there is delay of '.($delayMicroSec / 1000000).' seconds to allow for a retry interval.');
        usleep($delayMicroSec);

    } elseif (MAX_RETRY_COUNT < $executionCount) {
        Logger::output('max retry count exceeded.', ['maxRetryCount' => MAX_RETRY_COUNT]);
        break;
    }
    $executionCount++;

    /*
     * mongodb transaction start
     */

    $session = $mongodb->startSession();
    $session->startTransaction([]);
    Logger::output('transaction start.', ['executionCount' => $executionCount]);

    /*
     * issue new user id
     */

    try {
        $registrationSequenceCollection = $sampleDb->selectCollection('registrationSequence');
        $seq = $registrationSequenceCollection->findOneAndUpdate(
            ['groupId' => $groupId],
            ['$inc' => ['count' => 1]],
            ['returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER, 'session' => $session]
        );
        $newUserId = $seq['count'];

    } catch (\MongoDB\Driver\Exception\CommandException $e) {
        if ($e->getCode() === MONGODB_EXCEPTION_CODE_WRITE_CONFLICT) {
            Logger::output('transaction write conflict occurred.', ['executionCount' => $executionCount]);
            $session->abortTransaction();
            continue;
        }

        $session->abortTransaction();
        throw $e;

    } catch (\Exception $e) {
        $session->abortTransaction();
        throw $e;
    }

    /*
     * register user data
     */

    $nameList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    $continentList = ['Africa', 'Antarctic', 'Asia', 'Australia', 'Europe', 'North America', 'South America'];
    $userInfo = [
        'userId' => $newUserId,
        'name' => $nameList[mt_rand(0, 9)],
        'age' => mt_rand(0, 100),
        'residence' => $continentList[mt_rand(0, 6)]
    ];

    try {
        $userCollection = $sampleDb->selectCollection('user');
        $userInsertedResult = $userCollection->insertOne($userInfo);
        if ($userInsertedResult->getInsertedCount() !== 1) {
            Logger::output('failed to register user.', ['executionCount' => $executionCount]);
            $session->abortTransaction();
            continue;
        }

    } catch(\MongoDB\Driver\Exception\BulkWriteException $e) {
        $writeResult = $e->getWriteResult();
        foreach ($writeResult->getWriteErrors() as $error) {

            if ($error->getCode() === MONGODB_EXCEPTION_CODE_DUPLICATE_KEY) {
                Logger::output('already been registered.', [
                    'executionCount' => $executionCount, 'errorMessage' => $error->getMessage()
                ]);
                $session->abortTransaction();
                continue;
            }
        }

        $session->abortTransaction();
        throw $e;

    } catch (\Exception $e) {
        $session->abortTransaction();
        throw $e;
    }

    /*
     * mongodb transaction commit
     */

    $session->commitTransaction();
    Logger::output('transaction commit.', ['executionCount' => $executionCount]);
    Logger::output('registered user.', [
        'objectId' => $userInsertedResult->getInsertedId(),
        'userId' => $newUserId
    ]);

    break;

} while(true);

Logger::output('finish.');