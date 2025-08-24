<?phpclass Booking {
    public int $bookingID;
    public int $travellerID;
    public int $vehicleID;
    public int $guideID;
    public int $routeID;
    public float $totalCost;
    public DateTime $bookingDate;
    public string $status;

    public function __construct(
        int $bookingID,
        int $travellerID,
        int $vehicleID,
        int $guideID,
        int $routeID,
        float $totalCost,
        DateTime $bookingDate,
        string $status
    ) {
        $this->bookingID = $bookingID;
        $this->travellerID = $travellerID;
        $this->vehicleID = $vehicleID;
        $this->guideID = $guideID;
        $this->routeID = $routeID;
        $this->totalCost = $totalCost;
        $this->bookingDate = $bookingDate;
        $this->status = $status;
    }

    public function confirmBooking() {
        // Logic to confirm booking
    }

    public function cancelBooking() {
        // Logic to cancel booking
    }

    public function updateStatus(string $newStatus) {
        $this->status = $newStatus;
        // Optionally update in DB here
    }
}
