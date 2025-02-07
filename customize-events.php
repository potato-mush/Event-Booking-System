<form class="customize-event-form">
    <!-- Event Name, Date, and Time grouped together -->
    <div class="form-group">
        <h3 for="event-name">Event Name/Title</h3>
        <input type="text" id="event-name" name="event-name">

        <h3 for="event-date">Event Date</h3>
        <input type="date" id="event-date" name="event-date">

        <h3>Event Time</h3>
        <div class="time-group">
            <label for="event-time-start">Start</label>
            <input type="time" id="event-time-start" name="event-time-start">

            <label for="event-time-end">End</label>
            <input type="time" id="event-time-end" name="event-time-end">
        </div>
    </div>

    <!-- Event Theme and Number of Guests grouped together -->
    <div class="form-group">
        <h3 for="event-theme">Event Theme</h3>
        <input type="text" id="event-theme" name="event-theme">

        <h3 for="number-of-guests">Number of Guests</h3>
        <input type="number" id="number-of-guests" name="number-of-guests" min="1" max="1000">
    </div>

    <!-- Seating Arrangement -->
    <div class="form-group">
        <h3>Seating Arrangement</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="round-tables" name="seating-arrangement" value="round-tables"><label for="round-tables">Round Tables</label></div>
            <div><input type="checkbox" id="banquet-style" name="seating-arrangement" value="banquet-style"><label for="banquet-style">Banquet Style</label></div>
            <div><input type="checkbox" id="theatre-style" name="seating-arrangement" value="theatre-style"><label for="theatre-style">Theatre Style</label></div>
            <div><input type="checkbox" id="custom-seating" name="seating-arrangement" value="custom"><label for="custom-seating">Custom</label><input type="text" name="custom-seating-text" placeholder="Specify Custom Arrangement"></div>
        </div>
    </div>

    <!-- Menu Type -->
    <div class="form-group">
        <h3>Menu Type</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="buffet" name="menu-type" value="buffet"><label for="buffet">Buffet</label></div>
            <div><input type="checkbox" id="plated-meals" name="menu-type" value="plated-meals"><label for="plated-meals">Plated Meals</label></div>
            <div><input type="checkbox" id="cocktail" name="menu-type" value="cocktail"><label for="cocktail">Cocktail/Canapes</label></div>
        </div>
    </div>

    <!-- Additional Services -->
    <div class="form-group">
        <h3>Additional Services</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="invitation-cards" name="additional-services" value="invitation-cards"><label for="invitation-cards">Invitation Cards</label></div>
            <div><input type="checkbox" id="souvenirs" name="additional-services" value="souvenirs"><label for="souvenirs">Souvenirs</label></div>
            <div><input type="checkbox" id="photography" name="additional-services" value="photography"><label for="photography">Event Photography/Videography</label></div>
            <div><input type="checkbox" id="custom-services" name="additional-services" value="custom"><label for="custom-services">Custom</label><input type="text" name="custom-services-text" placeholder="Specify Custom Service"></div>
        </div>
    </div>

    <!-- Preferred Entertainment -->
    <div class="form-group">
        <h3>Preferred Entertainment</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="dj" name="preferred-entertainment" value="dj"><label for="dj">DJ</label></div>
            <div><input type="checkbox" id="live-band" name="preferred-entertainment" value="live-band"><label for="live-band">Live Band</label></div>
            <div><input type="checkbox" id="host" name="preferred-entertainment" value="host"><label for="host">Host/Emcee</label></div>
            <div><input type="checkbox" id="audio-visual" name="preferred-entertainment" value="audio-visual"><label for="audio-visual">Audio-Visual Setup</label></div>
            <div><input type="checkbox" id="dance-floor" name="preferred-entertainment" value="dance-floor"><label for="dance-floor">Dance Floor</label></div>
            <div><input type="checkbox" id="photo-booth" name="preferred-entertainment" value="photo-booth"><label for="photo-booth">Photo Booth</label></div>
            <div><input type="checkbox" id="other-entertainment" name="preferred-entertainment" value="other"><label for="other-entertainment">Other</label><input type="text" name="other-entertainment-text" placeholder="Specify Other Entertainment"></div>
        </div>
    </div>

    <!-- Event Type -->
    <div class="form-group">
        <h3>Event Type</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="debut" name="event-type" value="debut"><label for="debut">Debut Event</label></div>
            <div><input type="checkbox" id="wedding" name="event-type" value="wedding"><label for="wedding">Wedding Event</label></div>
            <div><input type="checkbox" id="corporate" name="event-type" value="corporate"><label for="corporate">Corporate Event</label></div>
            <div><input type="checkbox" id="kids-party" name="event-type" value="kids-party"><label for="kids-party">Kid's Party Event</label></div>
            <div><input type="checkbox" id="private-party" name="event-type" value="private-party"><label for="private-party">Private Party Event</label></div>
            <div><input type="checkbox" id="other-event" name="event-type" value="other"><label for="other-event">Other</label><input type="text" name="other-event-text" placeholder="Specify Event Type"></div>
        </div>
    </div>

    <!-- Decoration -->
    <div class="form-group">
        <h3>Decoration</h3>
        <div class="checkbox-group">
            <div><input type="checkbox" id="balloons" name="decoration" value="balloons"><label for="balloons">Balloons</label></div>
            <div><input type="checkbox" id="flowers" name="decoration" value="flowers"><label for="flowers">Flower Arrangement</label></div>
            <div><input type="checkbox" id="led-lights" name="decoration" value="led-lights"><label for="led-lights">LED lights</label></div>
            <div><input type="checkbox" id="backdrop" name="decoration" value="backdrop"><label for="backdrop">Backdrop/Photo Wall</label></div>
            <div><input type="checkbox" id="centerpieces" name="decoration" value="centerpieces"><label for="centerpieces">Table Centerpieces</label></div>
            <div><input type="checkbox" id="other-decoration" name="decoration" value="other"><label for="other-decoration">Other</label><input type="text" name="other-decoration-text" placeholder="Specify Other Decoration"></div>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit">Pay</button>
</form>