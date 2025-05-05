function updatePosition() {
    setInterval(() => {
        fetch('/api/getLatestPosition')
        .then(response => response.json())
        .then(data => {
            const newPosition = { lat: data.lat, lng: data.lng };
            marker.setPosition(newPosition);
            map.panTo(newPosition);
        })
        .catch(error => console.error('Error fetching position:', error));
    }, 1000); // Update every second
}