jQuery(document).ready(function ($) {
    const groups = ["billing", "shipping"];

    function coordinateString(location) {
        return `${location.lat()},${location.lng()}`;
    }

    function updateOtherCoordinate(group, coordinateValue) {
        console.log("Other coordinate");

        const otherGroup = groups.find((i) => i !== group);
        if (!otherGroup) return;

        const otherCoordinateInput = $(`#${otherGroup}_coordinate`);
        if (!otherCoordinateInput.length) return;

        otherCoordinateInput.val(coordinateValue).trigger("update_checkout", {
            update_shipping_method: true,
        });
    }

    function updateCoordinate(group, coordinateInput, location) {
        const coordinateValue = coordinateString(location);
        coordinateInput.val(coordinateValue);

        console.log("isBillingOnly:", isBillingOnly());
        console.log("isShipToDifferentAddress:", isShipToDifferentAddress());

        if (!isBillingOnly() && !isShipToDifferentAddress()) {
            return updateOtherCoordinate(group, coordinateValue);
        }

        coordinateInput.trigger("update_checkout", { update_shipping_method: true });
    }

    function isBillingOnly() {
        return !document.getElementById("ship-to-different-address-checkbox");
    }

    function isShipToDifferentAddress() {
        const shipToDifferentAddressSelector = "#ship-to-different-address-checkbox";
        return $(shipToDifferentAddressSelector).is(":checked");
    }

    $("form.woocommerce-checkout").on("change", 'input[name="payment_method"]', function () {
        $(document.body).trigger("update_checkout", {
            update_shipping_method: true,
        });
    });

    groups.forEach((group) => {
        const mapId = `${group}_map`;
        const searchBoxId = `${group}_map_searchbox`;
        const groupSelector = `.woocommerce-${group}-fields__field-wrapper`;
        const coordinateFieldSelector = `#${group}_coordinate_field`;
        const coordinateInputSelector = `#${group}_coordinate`;
        const coordinateField = $(coordinateFieldSelector);
        const coordinateInput = $(coordinateInputSelector);

        if (!coordinateField.length || !coordinateInput.length) return;

        coordinateInput.on("change", function () {
            $("body").trigger("update_checkout");
        });
        coordinateField.hide();

        const mapWrapper = $("<p>", { class: "woocommerce-map-wrapper form-row form-row-wide" });
        const mapLabel = $("<label>", { text: checkoutI18n.LOCATION_LABEL });
        const mapContainer = $("<div>", { class: "woocommerce-map-container", id: mapId });
        const [lat, lng] = coordinateInput.val()?.split(",") || [];
        const defaultCoordinate = { lat: parseFloat(lat) || -6.175392, lng: parseFloat(lng) || 106.8271528 };

        mapWrapper.append(mapLabel);
        mapWrapper.append(mapContainer);

        if (!$(groupSelector).length) {
            return;
        }

        $(groupSelector).after(mapWrapper);

        const searchBoxWrapper = $("<span>", { class: "woocommerce-searchbox-wrapper gm-control-active gm-fullscreen-control" });
        const searchBoxInput = $("<input>", { id: searchBoxId, placeholder: checkoutI18n.SEARCHBOX_PLACEHOLDER });

        searchBoxInput.on("keydown", function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        });

        searchBoxWrapper.append(searchBoxInput);

        const map = new google.maps.Map(document.getElementById(mapId), {
            center: defaultCoordinate,
            zoom: 13,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: google.maps.ControlPosition.BOTTOM_LEFT,
            },
        });

        const marker = new google.maps.Marker({
            position: defaultCoordinate,
            map: map,
        });

        const searchBox = new google.maps.places.SearchBox(searchBoxInput.get(0));

        map.controls[google.maps.ControlPosition.TOP_LEFT].push(searchBoxWrapper.get(0));

        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });

        map.addListener("click", (event) => {
            marker.setPosition(event.latLng);
            updateCoordinate(group, coordinateInput, event.latLng);
        });

        map.addListener("click", (event) => {
            event.stop();
        });

        searchBox.addListener("places_changed", function () {
            const places = searchBox.getPlaces();
            if (places.length === 0) return;

            const bounds = new google.maps.LatLngBounds();
            places.forEach((place) => {
                if (!place.geometry) return;

                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }

                marker.setPosition(place.geometry.location);
                updateCoordinate(group, coordinateInput, place.geometry.location);
            });

            map.fitBounds(bounds);
        });
    });
});
