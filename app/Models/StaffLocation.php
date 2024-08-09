<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;

class StaffLocation extends Model
{
    protected $collection = 'staff_locations';

    protected $client;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Initialize the MongoDB client
        $this->client = app('mongodb');
    }

    public function getCollection()
    {
        return $this->client->selectCollection(env('MONGODB_DATABASE'), $this->collection);
    }

    // Method to get all staff locations
    public function getAllStaffLocations()
    {
        return $this->getCollection()->find()->toArray();
    }

    // Method to find a staff location by UUID
    public function findByUUID($uuid)
    {
        return $this->getCollection()->find(['uuid' => $uuid])->toArray();
    }

    // Method to insert a staff location
    public function insertStaffLocation($data)
    {
        $data['created_at'] = new UTCDateTime((new \DateTime())->getTimestamp() * 1000);
        return $this->getCollection()->insertOne($data);
    }

    // Method to update a staff location by UUID
    public function updateStaffLocation($uuid, $data)
    {
        return $this->getCollection()->updateOne(
            ['uuid' => $uuid],
            ['$set' => $data]
        );
    }

    // Method to delete a staff location by UUID
    public function deleteStaffLocation($uuid)
    {
        return $this->getCollection()->deleteOne(['uuid' => $uuid]);
    }

    // Method to find staff locations with a query
    public function findWithQuery($pipeline)
    {
        return $this->getCollection()->aggregate($pipeline)->toArray();
    }    
}
