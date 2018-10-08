<?php

class ApplicantForVacancyList extends ohrmListConfigurationFactory
{

    protected function init()
    {

        $header1 = new ListHeader();
        $header2 = new ListHeader();
        $header3 = new ListHeader();

        $header1->populateFromArray(array(
            'name' => 'Candidate',
            'width' => '34%',
            'elementType' => 'label',
            'elementProperty' => array('getter' => 'getFirstName'),
        ));

        $header2->populateFromArray(array(
            'name' => 'Date Of Applicant',
            'width' => '33%',
            'elementType' => 'label',
            'elementProperty' => array('getter' => 'getDateOfApplication'),
        ));

        $header3->populateFromArray(array(
            'name' => 'Status',
            'width' => '33%',
            'elementType' => 'label',
            'elementProperty' => array('getter' => 'getStatus'),
        ));
        $this->headers = array($header1, $header2, $header3);
    }

    public function getClassName()
    {
        return 'JobVacancy';
    }

}

?>
