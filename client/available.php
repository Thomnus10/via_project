<?php
session_start();
$title = "Available Bookings";
$activePage = "available";

if (isset($_GET['get_availability'])) {
    header('Content-Type: application/json');
    require '../dbcon.php';

    // Date range (next 60 days)
    $start_date = date("Y-m-d");
    $end_date = date("Y-m-d", strtotime("+60 days"));

    // First, get all active trucks
    $trucks_query = "SELECT truck_id, truck_no, truck_type FROM trucks WHERE status = 'Available'";
    $trucks_result = $con->query($trucks_query);
    
    $all_trucks = [];
    $truck_id_map = [];
    $truck_types = [];
    
    while ($row = $trucks_result->fetch_assoc()) {
        $all_trucks[] = $row['truck_id'];
        $truck_id_map[$row['truck_id']] = $row['truck_no'];
        $truck_types[$row['truck_id']] = $row['truck_type'];
    }
    $total_trucks = count($all_trucks);
    
    // If no trucks are available at all, return empty array
    if ($total_trucks === 0) {
        echo json_encode([]);
        exit();
    }

    // Get all booked trucks by date with improved query
    // This query finds all trucks that are booked for each date in our range
    $sql = "SELECT 
                t.truck_id,
                t.truck_no,
                t.truck_type,
                DATE(s.start_time) as start_date,
                DATE(s.end_time) as end_date
            FROM schedules s
            JOIN trucks t ON s.truck_id = t.truck_id
            JOIN deliveries d ON s.schedule_id = d.schedule_id
            WHERE 
                (
                    (DATE(s.start_time) BETWEEN ? AND ?) OR 
                    (DATE(s.end_time) BETWEEN ? AND ?) OR
                    (DATE(s.start_time) <= ? AND DATE(s.end_time) >= ?)
                )
                AND d.delivery_status NOT IN ('Cancelled', 'Completed')
            ORDER BY DATE(s.start_time)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create a date-based booking map for each truck
    $booked_trucks_by_date = [];
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['start_date']);
        $end = new DateTime($row['end_date']);
        
        // For each day in the booking period, mark the truck as booked
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval, $end->modify('+1 day'));
        
        foreach ($daterange as $date) {
            $date_str = $date->format('Y-m-d');
            if (!isset($booked_trucks_by_date[$date_str])) {
                $booked_trucks_by_date[$date_str] = [];
            }
            $booked_trucks_by_date[$date_str][] = $row['truck_id'];
        }
    }
    $stmt->close();

    // Generate calendar events
    $events = [];
    $current = strtotime($start_date);
    $end = strtotime($end_date);

    while ($current <= $end) {
        $date = date('Y-m-d', $current);
        $booked_ids = isset($booked_trucks_by_date[$date]) ? $booked_trucks_by_date[$date] : [];
        
        // Calculate available trucks
        $available_ids = array_diff($all_trucks, $booked_ids);
        $available_count = count($available_ids);

        if ($available_count > 0) {
            // Group available trucks by type
            $available_by_type = [];
            foreach ($available_ids as $truck_id) {
                $type = $truck_types[$truck_id] ?? 'Unknown';
                if (!isset($available_by_type[$type])) {
                    $available_by_type[$type] = [];
                }
                $available_by_type[$type][] = $truck_id_map[$truck_id];
            }
            
            // Format available trucks by type for display
            $type_display = [];
            foreach ($available_by_type as $type => $trucks) {
                $type_display[] = "$type: " . implode(', ', $trucks);
            }
            
            // Create a simple title for the calendar
            $title = "$available_count Truck" . ($available_count > 1 ? "s" : "") . " Available";
            
            // Choose color based on availability percentage
            $availability_percentage = ($available_count / $total_trucks) * 100;
            $color = '';
            
            if ($availability_percentage == 100) {
                $color = '#a8ffa8'; // All trucks available - light green
            } elseif ($availability_percentage >= 50) {
                $color = '#90EE90'; // More than half available - medium green
            } elseif ($availability_percentage > 20) {
                $color = '#FFD700'; // Some trucks available - gold
            } else {
                $color = '#FFA07A'; // Few trucks available - light salmon
            }

            $events[] = [
                'title' => $title,
                'start' => $date,
                'color' => $color,
                'textColor' => '#000',
                'extendedProps' => [
                    'available' => $available_count,
                    'availableByType' => $available_by_type,
                    'trucks' => array_map(function($id) use ($truck_id_map) {
                        return $truck_id_map[$id];
                    }, $available_ids),
                    'truckDetails' => array_reduce($available_ids, function($carry, $id) use ($truck_id_map, $truck_types) {
                        $carry[] = [
                            'id' => $id,
                            'number' => $truck_id_map[$id],
                            'type' => $truck_types[$id] ?? 'Unknown'
                        ];
                        return $carry;
                    }, []),
                    'date' => $date,
                    'typeDisplay' => $type_display
                ]
            ];
        } else {
            // Add dates with no availability too, with different styling
            $events[] = [
                'title' => 'No Trucks Available',
                'start' => $date,
                'color' => '#f5f5f5',
                'textColor' => '#999',
                'extendedProps' => [
                    'available' => 0,
                    'date' => $date
                ]
            ];
        }
        $current = strtotime('+1 day', $current);
    }

    echo json_encode($events);
    exit();
}

// Normal page load
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

ob_start();
?>


<body>
    <h1>Available Truck Booking Dates</h1>

    <div class="tab-buttons">
        <button class="tab-button active" data-view="list-view">List View</button>
        <button class="tab-button" data-view="calendar-view">Calendar View</button>
    </div>

    <div id="list-view" class="active-view">
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #a8ffa8;"></div>
                <span>All Trucks Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #90EE90;"></div>
                <span>Most Trucks Available (50%+)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #FFD700;"></div>
                <span>Some Trucks Available (20-50%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #FFA07A;"></div>
                <span>Few Trucks Available (< 20%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #f5f5f5;"></div>
                <span>No Trucks Available</span>
            </div>
        </div>
        
        <div class="filter-controls">
            <div class="filter-group">
                <span class="filter-label">Truck Type:</span>
                <select id="truck-type-filter" class="filter-select">
                    <option value="all">All Types</option>
                    <!-- Types will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Availability:</span>
                <select id="availability-filter" class="filter-select">
                    <option value="all">Any Availability</option>
                    <option value="high">High (50%+)</option>
                    <option value="medium">Medium (20-50%)</option>
                    <option value="low">Low (< 20%)</option>
                </select>
            </div>
        </div>
        
        <div class="event-list" id="event-list-container">
            <div class="loading">Loading available dates...</div>
        </div>
    </div>

    <div id="calendar-view">
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #a8ffa8;"></div>
                <span>All Trucks Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #90EE90;"></div>
                <span>Most Trucks Available (50%+)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #FFD700;"></div>
                <span>Some Trucks Available (20-50%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #FFA07A;"></div>
                <span>Few Trucks Available (< 20%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #f5f5f5;"></div>
                <span>No Trucks Available</span>
            </div>
        </div>
        
        <div id='calendar'></div>
        <div id="tooltip" class="tooltip"></div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const views = document.querySelectorAll('#list-view, #calendar-view');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const viewId = this.getAttribute('data-view');
                    
                    // Deactivate all buttons and views
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    views.forEach(view => view.classList.remove('active-view'));
                    
                    // Activate clicked button and corresponding view
                    this.classList.add('active');
                    document.getElementById(viewId).classList.add('active-view');
                    
                    // Initialize calendar if calendar view is selected
                    if (viewId === 'calendar-view' && !calendarInitialized) {
                        calendar.render();
                        calendarInitialized = true;
                    }
                });
            });
            
            // Tooltip setup for calendar
            const tooltip = document.getElementById('tooltip');
            
            // Track truck types for filtering
            let availableTruckTypes = new Set();
            let allEvents = [];
            
            // Calendar setup
            var calendarInitialized = false;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: {
                    url: new URL(window.location.href).pathname,
                    extraParams: {
                        get_availability: true
                    },
                    failure: function(error) {
                        console.error('Error loading availability:', error);
                        alert('Error loading available dates. Please try again.');
                    },
                    success: function(events) {
                        // Process events to update truck type filter
                        events.forEach(event => {
                            if (event.extendedProps && event.extendedProps.truckDetails) {
                                event.extendedProps.truckDetails.forEach(truck => {
                                    if (truck.type) availableTruckTypes.add(truck.type);
                                });
                            }
                        });
                        
                        // Update truck type filter options
                        const truckTypeFilter = document.getElementById('truck-type-filter');
                        truckTypeFilter.innerHTML = '<option value="all">All Types</option>';
                        
                        Array.from(availableTruckTypes).sort().forEach(type => {
                            const option = document.createElement('option');
                            option.value = type;
                            option.textContent = type;
                            truckTypeFilter.appendChild(option);
                        });
                    }
                },
                eventMouseEnter: function(info) {
                    const event = info.event;
                    
                    // Only show tooltip for events with available trucks
                    if (event.extendedProps.available > 0) {
                        const date = new Date(event.extendedProps.date);
                        const formattedDate = date.toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        });
                        
                        // Build tooltip content
                        let tooltipContent = `<div class="tooltip-title">${formattedDate}</div>`;
                        tooltipContent += `<div class="tooltip-content">`;
                        tooltipContent += `<div>${event.extendedProps.available} truck${event.extendedProps.available > 1 ? 's' : ''} available</div>`;
                        
                        // Show trucks by type
                        if (event.extendedProps.availableByType) {
                            Object.entries(event.extendedProps.availableByType).forEach(([type, trucks]) => {
                                tooltipContent += `<div class="truck-type-group">`;
                                tooltipContent += `<div class="truck-type-title">${type}:</div>`;
                                tooltipContent += `<div>${trucks.join(', ')}</div>`;
                                tooltipContent += `</div>`;
                            });
                        }
                        
                        tooltipContent += `</div>`;
                        
                        // Position and show tooltip
                        tooltip.innerHTML = tooltipContent;
                        tooltip.style.display = 'block';
                        tooltip.style.left = info.jsEvent.pageX + 10 + 'px';
                        tooltip.style.top = info.jsEvent.pageY + 10 + 'px';
                    }
                },
                eventMouseLeave: function() {
                    // Hide tooltip
                    tooltip.style.display = 'none';
                },
                eventClick: function(info) {
                    const event = info.event;
                    
                    // Only allow booking if trucks are available
                    if (event.extendedProps.available > 0) {
                        const date = event.extendedProps.date;
                        
                        // Build truck list
                        let truckListByType = '';
                        if (event.extendedProps.availableByType) {
                            Object.entries(event.extendedProps.availableByType).forEach(([type, trucks]) => {
                                truckListByType += `${type}: ${trucks.join(', ')}\n`;
                            });
                        }
                        
                        if (confirm(`Available Truck(s) on ${date}:\n\n${truckListByType}\nProceed to book?`)) {
                            window.location.href = 'booking.php?date=' + date;
                        }
                    } else {
                        alert('No trucks available on this date.');
                    }
                },
                dayCellDidMount: function(info) {
                    // This will run for each day cell when it's created
                    // We can style cells differently based on their data
                }
            });
            
            // Load availability for the list view
            // Use the current URL with additional params instead of appending to avoid issues with existing query params
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('get_availability', 'true');
            
            function updateListView(filterType = 'all', filterAvailability = 'all') {
                // Apply filters to the events
                const filteredEvents = allEvents.filter(event => {
                    // Skip events without availability
                    if (event.extendedProps.available === 0) {
                        // Only include no-availability events when showing all types
                        return filterAvailability === 'all' && filterType === 'all';
                    }
                    
                    // Filter by truck type if not 'all'
                    let passesTypeFilter = true;
                    if (filterType !== 'all') {
                        // Check if any truck of this type is available
                        passesTypeFilter = event.extendedProps.availableByType && 
                                          event.extendedProps.availableByType[filterType] !== undefined;
                    }
                    
                    // Filter by availability level
                    let passesAvailabilityFilter = true;
                    if (filterAvailability !== 'all') {
                        const totalTruckCount = Object.values(event.extendedProps.availableByType || {})
                            .reduce((sum, trucks) => sum + trucks.length, 0);
                        const availabilityPercentage = event.extendedProps.available / totalTruckCount * 100;
                        
                        if (filterAvailability === 'high' && availabilityPercentage < 50) {
                            passesAvailabilityFilter = false;
                        } else if (filterAvailability === 'medium' && (availabilityPercentage < 20 || availabilityPercentage >= 50)) {
                            passesAvailabilityFilter = false;
                        } else if (filterAvailability === 'low' && availabilityPercentage >= 20) {
                            passesAvailabilityFilter = false;
                        }
                    }
                    
                    return passesTypeFilter && passesAvailabilityFilter;
                });
                
                // Update the list view with filtered events
                const container = document.getElementById('event-list-container');
                container.innerHTML = '';
                
                if (filteredEvents.length === 0) {
                    container.innerHTML = '<div class="all-full-message">No trucks match your filter criteria. Try adjusting your filters.</div>';
                    return;
                }
                
                // Sort events by date
                filteredEvents.sort((a, b) => new Date(a.extendedProps.date) - new Date(b.extendedProps.date));
                
                // Create event cards
                filteredEvents.forEach(event => {
                    const dateObj = new Date(event.extendedProps.date);
                    const formattedDate = dateObj.toLocaleDateString('en-US', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    const card = document.createElement('div');
                    card.className = 'event-card';
                    card.style.borderLeft = `5px solid ${event.color}`;
                    
                    let cardContent = `<div class="event-date">${formattedDate}</div>`;
                    
                    if (event.extendedProps.available > 0) {
                        cardContent += `<div class="event-available">${event.extendedProps.available} Truck${event.extendedProps.available > 1 ? 's' : ''} Available</div>`;
                        
                        // Show trucks by type
                        if (event.extendedProps.availableByType) {
                            cardContent += `<div class="event-truck-list">`;
                            Object.entries(event.extendedProps.availableByType).forEach(([type, trucks]) => {
                                // If filtering by type, only show that type
                                if (filterType === 'all' || filterType === type) {
                                    cardContent += `<div class="event-truck-type">${type}:</div>`;
                                    cardContent += `<div>${trucks.join(', ')}</div>`;
                                }
                            });
                            cardContent += `</div>`;
                        }
                        
                        // Add click event for booking
                        card.addEventListener('click', function() {
                            if (confirm(`Book a truck for ${formattedDate}?`)) {
                                window.location.href = 'booking.php?date=' + event.extendedProps.date;
                            }
                        });
                    } else {
                        cardContent += `<div class="event-available no-availability">No Trucks Available</div>`;
                    }
                    
                    card.innerHTML = cardContent;
                    container.appendChild(card);
                });
            }
            
            // Setup filters
            const truckTypeFilter = document.getElementById('truck-type-filter');
            const availabilityFilter = document.getElementById('availability-filter');
            
            truckTypeFilter.addEventListener('change', function() {
                updateListView(this.value, availabilityFilter.value);
            });
            
            availabilityFilter.addEventListener('change', function() {
                updateListView(truckTypeFilter.value, this.value);
            });
            
            // Load availability data
            fetch(currentUrl.toString())
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(events => {
                    // Store events for filtering
                    allEvents = events;
                    
                    // Update truck type filter with available types
                    availableTruckTypes = new Set();
                    events.forEach(event => {
                        if (event.extendedProps && event.extendedProps.truckDetails) {
                            event.extendedProps.truckDetails.forEach(truck => {
                                if (truck.type) availableTruckTypes.add(truck.type);
                            });
                        }
                    });
                    
                    // Update truck type filter options
                    truckTypeFilter.innerHTML = '<option value="all">All Types</option>';
                    
                    Array.from(availableTruckTypes).sort().forEach(type => {
                        const option = document.createElement('option');
                        option.value = type;
                        option.textContent = type;
                        truckTypeFilter.appendChild(option);
                    });
                    
                    // Initialize list view
                    updateListView();
                })
                .catch(error => {
                    console.error('Error loading availability:', error);
                    document.getElementById('event-list-container').innerHTML = 
                        '<div class="all-full-message">Error loading available dates. Please try again. Error: ' + error.message + '</div>';
                });
        });
    </script>
</body>


<?php
$content = ob_get_clean();
include "../layout/client_layout.php";
?>