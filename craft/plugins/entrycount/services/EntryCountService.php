<?php
namespace Craft;

/**
 * Entry Count Service
 */
class EntryCountService extends BaseApplicationComponent
{
    /**
     * Returns count
     *
	 * @param int $entryId
	 *
	 * @return EntryCountModel
     */
    public function getCount($entryId)
    {
        // create new model
        /*
        'Model' being an object from the EntryCountModel class.
        */
        $entryCountModel = new EntryCountModel();

        // get record from DB
        /*
        Creates $entryCountRecord property, then calls the static model() method
        on the EntryCountRecord. This will return an instance of the record back
        to us. Then, we'll call the method findByAttributes() and we'll pass an array
        with the attribute 'entryId' and the value that was passed in to $entryId.
        */
        $entryCountRecord = EntryCountRecord::model()->findByAttributes(array('entryId' => $entryId));

        if ($entryCountRecord)
        {
            // populate model from record
            /*
            If $entryCountRecord exists, we're going to populate the 
            model with it. We do this by calling the static populateModel() 
            method and passing in our $entryCountRecord.
            */
            $entryCountModel = EntryCountModel::populateModel($entryCountRecord);
        }

        return $entryCountModel;
    }

    /**
     * Returns counted entries
     *
     * @return ElementCriteriaModel
     */
    public function getEntries()
    {
        // gets all records from DB 
        /*
        $entryCountRecords fetchs all of the entrycount records from
        the database by again calling the static model() method on the 
        EntryCountRecord. We're going to call findAll() and, instead of
        passing in attribute, we're going to specify that the returned
        records should be ordered by count descending.
        */
        $entryCountRecords = EntryCountRecord::model()->findAll(array(
            'order'=>'count desc'
        ));
    /***** 
            Now we've got all the entrycount records. But what this method
            should actually return is entries. What we're going to do is
            loop through the entrycount records and grab all of the entry ids.
     *****/

        // get entry ids from records
        $entryIds = array();

        /*
        Loops through each of the entryCountRecords, assigning an $entryId
        from each entryCountRecord. After the $entryIds array is populated, we
        can get the entries with those ids.
        */
        foreach ($entryCountRecords as $entryCountRecord)
        {
            $entryIds[] = $entryCountRecord->entryId;
        }

        /* 
        We now create a new criteria by using Craft's 'elements' service.
        We do this by calling the craft() global function, followed by the 
        'elements' service, and the method getCriteria(). We just have to pass
        in a string with the element type, which is 'Entry'.
        */
        $criteria = craft()->elements->getCriteria('Entry');

        /*
        $criteria is an *ElementCriteriaModel, and we want to make sure that
        only entries that have been counted are returned by our criteria. We 
        do that by setting the id to the $entryIds array.
        */
        $criteria->id = $entryIds;

        /*
        We also want to specify that our entry is returned in the same order as
        our entrycount records. We do this by setting fixedOrder to true.
        */
        $criteria->fixedOrder = true;

        return $criteria;
    }

/*****
        The first thing we did is grab all records from the database, ordered
        by count descending. The next thing we do is get the entry ids from the
        record. We do this with a foreach loop that loops over all the records and
        populates the $entryIds array with each record's entryId. Next, we create 
        a criteria for the 'Entry' *ElementType. We then filter the criteria by our 
        entryIds and we enable a fixed order. Finally we return the $criteria.
*****/


    /**
     * Increment count
     *
	 * @param int $entryId
     */
    public function increment($entryId)
    {
        // check if action should be ignored
        if ($this->_ignoreAction())
        {
            return;
        }

        // get record from DB
        $entryCountRecord = EntryCountRecord::model()->findByAttributes(array('entryId' => $entryId));

        // if exists then increment 'count' attribute.
        if ($entryCountRecord)
        {
            $entryCountRecord->setAttribute('count', $entryCountRecord->getAttribute('count') + 1);
        }

        // otherwise create a new record
        else
        {
            $entryCountRecord = new EntryCountRecord;
            $entryCountRecord->setAttribute('entryId', $entryId);
            $entryCountRecord->setAttribute('count', 1);
        }

        // save record in DB
        $entryCountRecord->save();
    }

    /**
     * Reset count
     *
	 * @param int $entryId
     */
    public function reset($entryId)
    {
        // get record from DB
        $entryCountRecord = EntryCountRecord::model()->findByAttributes(array('entryId' => $entryId));

        // if record exists then delete
        if ($entryCountRecord)
        {
            // delete record from DB
            $entryCountRecord->delete();
        }

        // log reset
        EntryCountPlugin::log(
            'Entry count with entry ID '.$entryId.' reset by '.craft()->userSession->getUser()->username,
            LogLevel::Info,
            true
        );

        // fire an onResetCount event
        $this->onResetCount(new Event($this, array('entryId' => $entryId)));
    }

    /**
     * On reset count
     *
     * @param Event $event
     */
    public function onResetCount($event)
    {
        $this->raiseEvent('onResetCount', $event);
    }

    // Helper methods
    // =========================================================================

    /**
     * Check if action should be ignored
     */
    private function _ignoreAction()
    {
        // get plugin settings
        $settings = craft()->plugins->getPlugin('entryCount')->getSettings();

        // check if logged in users should be ignored based on settings
        if ($settings->ignoreLoggedInUsers AND craft()->userSession->isLoggedIn())
        {
            return true;
        }

        // check if ip address should be ignored based on settings
        if ($settings->ignoreIpAddresses AND in_array(craft()->request->getIpAddress(), explode("\n", $settings->ignoreIpAddresses)))
        {
            return true;
        }
    }
}
