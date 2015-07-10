<?php

namespace Optisoop\Bundle\AdminBundle\Service;

/**
 * Class AnalyticsService
 *
 * This is the class that communicates with analytics api
 */
class AnalyticsService extends \Google_Service_Analytics
{
    /**
     * @var GoogleClient client
     *
     *
     */
    public $client;
    
    /*
     * Doctrine service
     */
    public $doctrine;

    /**
     * Constructor
     * @param GoogleClient $client
     */
    public function __construct(GoogleClient $client, $doctrine)
    {
        $this->client = $client;
        $this->doctrine = $doctrine;
        parent::__construct($client->getGoogleClient());
    }
    
    public function hasAccount() {
        try {
            $accounts = $this->management_accounts->listManagementAccounts(); 
            if (count($accounts->getItems()) > 0) {
                return true;
            }

            return false;

        } catch (\Exception $ex) {
           return false;
        }
     
    }
    
    public function getGoogleAnalitycsData($startDate, $endDate) {
       $answer = new \stdClass();
        
      try {
        //Step 2. Get the user's first view (profile) ID.

        $profileId = $this->getFirstProfileId();
        if (isset($profileId)) {
          // Step 3. Query the Core Reporting API.
          $results = $this->getResults($profileId);
          $report = $this->getReport($results->getProfileInfo()->getTableId());
          $reportDay = $this->getReportDay($profileId);
          $reportPageviewDay = $this->getReportPageviewDay($profileId);
//          $bouncesDay = $this->getReportBouncesDay($profileId);
//          $exitsDay = $this->getReportExitsDay($profileId);
//          $uniqeDay = $this->getReportUniqeDay($profileId);
   
          // Step 4. Output the results.
          $mainResult =  $this->printResults($results);
          $reportResult = $this->printReport($report);
          return array( $mainResult, 
                        $reportResult, 
                        $reportDay, 
                        $reportPageviewDay
                    );
        }
        
        
      } catch (apiServiceException $e) {
        // Error from the API.
        print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();

      } catch (Exception $e) {
        print 'There wan a general error : ' . $e->getMessage();
      }
    }

     private function getReportDay($profileId){
        $reportDay = array();
        //Report by day
          $sDate = new \DateTime('now');
          $eDate = new \DateTime('now');
          $sDate->modify('-6 days');
          for ($index = 0; $index < 6; $index++) {
              $sDate->modify('+1 day');
              $startDate = $sDate->format('Y-m-d');
              $reportDay[$startDate] = $this->getVisitDay($profileId, $startDate, $startDate);
          }
          return $reportDay;
    }
    
    private function getReportPageviewDay($profileId){
        $reportDay = array();
        //Report by day
          $sDate = new \DateTime('now');
          $eDate = new \DateTime('now');
          $sDate->modify('-6 days');
          for ($index = 0; $index < 6; $index++) {
              $sDate->modify('+1 day');
              $startDate = $sDate->format('Y-m-d');
              $reportDay[$startDate] = $this->getPageviewDay($profileId, $startDate, $startDate);
          }
          return $reportDay;
    }
    
    private function getReportBouncesDay($profileId){
        $reportDay = array();
        //Report by day
          $sDate = new \DateTime('now');
          $eDate = new \DateTime('now');
          $sDate->modify('-6 days');
          for ($index = 0; $index < 6; $index++) {
              $sDate->modify('+1 day');
              $startDate = $sDate->format('Y-m-d');
              $reportDay[$startDate] = $this->getBouncesDay($profileId, $startDate, $startDate);
          }
          return $reportDay;
    }
    
    private function getReportExitsDay($profileId){
        $reportDay = array();
        //Report by day
          $sDate = new \DateTime('now');
          $eDate = new \DateTime('now');
          $sDate->modify('-6 days');
          for ($index = 0; $index < 6; $index++) {
              $sDate->modify('+1 day');
              $startDate = $sDate->format('Y-m-d');
              $reportDay[$startDate] = $this->getExitsDay($profileId, $startDate, $startDate);
          }
          return $reportDay;
    }
    
    private function getReportUniqeDay($profileId){
        $reportDay = array();
        //Report by day
          $sDate = new \DateTime('now');
          $eDate = new \DateTime('now');
          $sDate->modify('-6 days');
          for ($index = 0; $index < 6; $index++) {
              $sDate->modify('+1 day');
              $startDate = $sDate->format('Y-m-d');
              $reportDay[$startDate] = $this->getUniqeDay($profileId, $startDate, $startDate);
          }
          return $reportDay;
    }
    
    public function getProfileItems() {
        $accounts = $this->management_accounts->listManagementAccounts();
         
        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            return $items;
        } else {
            throw new Exception('No accounts found for this user.');
        }
    }
    
    public function getFirstprofileId() {
      $accounts = $this->management_accounts->listManagementAccounts();

      if (count($accounts->getItems()) > 0) {
        $items = $accounts->getItems();

        $firstAccountId = $items[0]->getId();

        $webproperties = $this->management_webproperties
            ->listManagementWebproperties($firstAccountId);

        if (count($webproperties->getItems()) > 0) {
          $items = $webproperties->getItems();
          $firstWebpropertyId = $items[0]->getId();

          $profiles = $this->management_profiles
              ->listManagementProfiles($firstAccountId, $firstWebpropertyId);

          if (count($profiles->getItems()) > 0) {
            $items = $profiles->getItems();
            return $items[0]->getId();

          } else {
            throw new Exception('No views (profiles) found for this user.');
          }
        } else {
          throw new Exception('No webproperties found for this user.');
        }
      } else {
        throw new Exception('No accounts found for this user.');
      }
    }

    public function getResults($profileId) {
       return $this->data_ga->get(
           'ga:' . $profileId,
           '2012-03-03',
           '2014-11-18',
           'ga:sessions,ga:pageviews,ga:users,ga:bounceRate,ga:avgSessionDuration,ga:pageviewsPerSession,ga:exits,ga:bounces');
    }

    public function getReport($tableId) {
        
       $optParams = array(
            'dimensions' => 'ga:country',
            'sort' => '-ga:visits',
            'filters' => 'ga:medium==organic',
            'max-results' => '25');

        return $this->data_ga->get(
            urldecode($tableId),
            '2010-01-01',
            '2014-11-18',
            'ga:visits,ga:sessions,ga:pageviews,ga:users,ga:bounceRate',
            $optParams);
    }

    public function getVisitDay($profileId, $startDate, $endDate) {
        $results = $this->data_ga->get(
           'ga:' . $profileId,
           $startDate,
           $endDate,
           'ga:visits');

        if (count($results->getRows()) > 0) {
          foreach ($results->getRows() as $row) {
              foreach ($row as $cell) {
                return htmlspecialchars($cell, ENT_NOQUOTES); 
              }
          }
        } else {
          return 0;
        }
    }
    
    public function getPageviewDay($profileId, $startDate, $endDate) {
        $results = $this->data_ga->get(
           'ga:' . $profileId,
           $startDate,
           $endDate,
           'ga:pageviews');

        if (count($results->getRows()) > 0) {
          foreach ($results->getRows() as $row) {
              foreach ($row as $cell) {
                return htmlspecialchars($cell, ENT_NOQUOTES); 
              }
          }
        } else {
          return 0;
        }
    }
    
    public function getBouncesDay($profileId, $startDate, $endDate) {
        $results = $this->data_ga->get(
           'ga:' . $profileId,
           $startDate,
           $endDate,
           'ga:bounces');

        if (count($results->getRows()) > 0) {
          foreach ($results->getRows() as $row) {
              foreach ($row as $cell) {
                return htmlspecialchars($cell, ENT_NOQUOTES); 
              }
          }
        } else {
          return 0;
        }
    }
    
    public function getExitsDay($profileId, $startDate, $endDate) {
        $results = $this->data_ga->get(
           'ga:' . $profileId,
           $startDate,
           $endDate,
           'ga:exits');

        if (count($results->getRows()) > 0) {
          foreach ($results->getRows() as $row) {
              foreach ($row as $cell) {
                return htmlspecialchars($cell, ENT_NOQUOTES); 
              }
          }
        } else {
          return 0;
        }
    }
    
    public function getUniqeDay($profileId, $startDate, $endDate) {
        $results = $this->data_ga->get(
           'ga:' . $profileId,
           $startDate,
           $endDate,
           'ga:users');

        if (count($results->getRows()) > 0) {
          foreach ($results->getRows() as $row) {
              foreach ($row as $cell) {
                return htmlspecialchars($cell, ENT_NOQUOTES); 
              }
          }
        } else {
          return 0;
        }
    }

    public function printResults($results) {
      if (count($results->getRows()) > 0) {
        $returnValues = array();
        $head = array();
        foreach ($results->getColumnHeaders() as $header) {
            $pos = strpos($header->name, ':');
            if ($pos === false) {
                $head[] = $header->name;
            }else{
                $arrName = explode(':', $header->name);
                $head[] = $arrName[1];
            }
        }

        foreach ($results->getRows() as $row) {
            $count = 0;
            foreach ($row as $cell) {
               if(isset($head[$count])){
                  $key = $head[$count];
                  if($key == 'bounceRate' || $key == 'pageviewsPerSession'){
                        $cell = number_format((float)$cell, 2, '.', '');  
                  }elseif($key == 'avgSessionDuration'){
                        $cell = $this->conversorSegundosHoras($cell);
                  }
                  $returnValues[$key] = htmlspecialchars($cell, ENT_NOQUOTES); 
               }
               $count++;
            }
        }
        return $returnValues;
      } else {
        return null;
      }
    }
    
    public function conversorSegundosHoras($tiempo_en_segundos) {
            $horas = floor($tiempo_en_segundos / 3600);
            $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
            $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
            return $horas . ':' . $minutos . ":" . round($segundos);
    }
    
    public function printReport($results) {
        $em = $this->doctrine->getManager();
        if (count($results->getRows()) > 0) {
          $returnValues = array();
          $head = array();
          foreach ($results->getColumnHeaders() as $header) {
              $pos = strpos($header->name, ':');
              if ($pos === false) {
                  $head[] = $header->name;
              }else{
                  $arrName = explode(':', $header->name);
                  $head[] = $arrName[1];
              }
          }

          
          foreach ($results->getRows() as $row) {
              $count = 0;
              $sub = array();
              foreach ($row as $cell) {
                  if(isset($head[$count])){
                      $key = $head[$count];
                      if($key == 'country' && $cell != '(not set)'){
                          $countryEntity = $em->getRepository('CoreBundle:Country')->findOneByName(strtolower(htmlspecialchars($cell, ENT_NOQUOTES)));
                          $sub['id'] = $countryEntity->getId();
                      }
                      if($key == 'bounceRate' || $key == 'avgTimeOnSite') $cell = number_format((float)$cell, 2, '.', '');
                      $sub[$key] = htmlspecialchars($cell, ENT_NOQUOTES); 
                  }
                 $count++;
              }
              $returnValues[] = $sub;
          }
          
          $mapValues = array();
          foreach ($returnValues as $key => $value) {
              if(isset($value['id'])) {
                  $mapValues[strtoupper($value['id'])] = intval($value['visits']);
                  unset($returnValues[$key]['id']);
              }
          }

          return array('header' => $head, 'value' => $returnValues, 'mapValues' => json_encode($mapValues));
        } else {
          return null;
        }
    }
}