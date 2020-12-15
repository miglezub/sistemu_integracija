<?php 
    if(isset($_SESSION['user_id'])) {
        $service = new Google_Service_Calendar($gClient);
        $calendarList = $service->calendarList->listCalendarList();
?>
<div class="form-group">
    <label for="googleCalendar">Pasirinkite prie kurio kalendoriaus pridėti įvykį</label>
    <select class="form-control" id="googleCalendar" onchange="checkFreebusy()">
        <?php
            while(true) {
                foreach ($calendarList->getItems() as $calendarListEntry) {
                    echo "<option value='" . $calendarListEntry->getId() . "'>" . $calendarListEntry->getSummary() . "</option>";
                }
                $pageToken = $calendarList->getNextPageToken();
                if ($pageToken) {
                    $optParams = array('pageToken' => $pageToken);
                    $calendarList = $service->calendarList->listCalendarList($optParams);
                } else {
                    break;
                }
            }
        ?>
    </select>
</div>
<?php } ?>