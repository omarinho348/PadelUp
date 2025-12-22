<?php
// Generic Observer interface for all events
interface Observer {
    public function update($event, $data);
}

// Generic Observable subject for all event types
class Observable {
    private $observers = [];

    public function attach(Observer $observer) {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer) {
        $this->observers = array_filter($this->observers, fn($obs) => $obs !== $observer);
    }

    public function notify($event, $data) {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }
}
