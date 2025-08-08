# ShippingSimulation
A real life example for a SortedLinkedList

### Requirements

- Implement a `SortedLinkedList` library that maintains its elements in sorted order.
- It should only hold string or int values, not both.

### Real Life Example with Shipping Simulation

With the present library,I'm simulating a shipping scenario:

- Packages can be sorted by priority levels (integers) or destination names (strings).
- If we receive an express order, we can place it at the front of the queue.
- It processes packages in order of their priority or destination.

### Simulation

Running **index.php** will execute the ShipMonkSimulation class, which simulates a typical day 
in a fulfillment warehouse. It demonstrates how orders from various customers with different 
shipping priorities are added to the fulfillment queue, processed according to urgency, and 
tracked for SLA risks. The simulation outputs order stats, highlights at-risk orders, and 
shows how the warehouse prioritizes and processes shipments throughout the morning shift. 

This provides a realistic overview of order management and fulfillment operations 
using the system and the SortedLinkedList library :)

### Usage
- Clone the repository.
- Run `composer install` to install dependencies.
- Execute `php index.php` to run the simulation.

### If You Prefer Docker

You can run this project using Docker.

#### Build and Run

1. Ensure you have Docker and Docker Compose installed.
2. Build and start the container:

   ```sh
   docker-compose up --build -d
    ```

This will install dependencies and run index.php as a CLI script inside the container.

3. To run index.php interactively inside the container:

    ```sh
    docker-compose run app php index.php
    ```

**Note:**
index.php is a command-line script. There is no web server or browser interface.

4. To run the shipmonk-rnd/dead-code-detector (PHPStan) static analysis tool, you can use the following command:

   ```sh
   docker-compose run app vendor/bin/dead-code-detector analyse --level=5 src/
   ```

5. To stop the container, run:

   ```sh
   docker-compose down --remove-orphans
   ```
6. To remove the container and its volumes, run:

    ```sh
    docker-compose down -v
    ```

### Disclaimer

This is a simplified simulation for educational purposes. 
This code was created to demonstrate the use of a `SortedLinkedList` in a real-world scenario.
Am a bit asleep, so instead of adding unit tests, I just wrote a simulation to show how it works.

I used PHPStorm to write this code, I did use its AI assistant in some parts to generate boilerplate code, 
but I wrote most of the logic myself.