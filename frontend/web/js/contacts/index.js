ymaps.ready(function () {
    var mapselector = $('#map__container');

    //render ya map
    var map = new ymaps.Map('map__container', {
            center: [mapselector.data('lat'), mapselector.data('lon')],
            zoom: 10,
            avoidFractionalZoom: false,
            controls: []
        },
        {
            autoFitToViewport: 'always',
        }
    );

    map.behaviors.disable('scrollZoom');
    map.controls.add('zoomControl', {position: {right: '20px', top: '20px'}});

    //init objectManager
    var objectManager = new ymaps.ObjectManager({clusterize: true});

    //create icon wrap
    var iconWrap = ymaps.templateLayoutFactory.createClass(
        '<div id="{{id}}" class="hrd__placemark">' +
        '<ul class="placemark__popover">' +
        '<li>{{properties.hintContent}}</li>' +
        '</ul>' +
        '<img src="/images/static/sprites/hrd_placemark.svg">' +
        '</div>',
        {
            build: function () {
                iconWrap.superclass.build.call(this);
                objectManager.objects.options.set({
                    hasBalloon: false,
                });

                let elem = document.getElementById(this._data.id);
                //show animation
                elem.style.animation = '1s show-placemark';

                binderPlacemark(); //костыль, срабатыват при каждом бинде, нужно оптимизировать

            },
            clear: function () {
                iconWrap.superclass.clear.call(this);
            },
        }
    );

    //placemarks array
    var placemarks = [];
    var i = 1;
    $('.placemark').each(function () {
        placemarks.push({
            type: 'Feature',
            id: 'placemark' + i++,
            geometry: {
                type: 'Point',
                coordinates: [$(this).data('lat'), $(this).data('lon')],
            },
            properties: {
                hideIcon: false,
                hintContent: $(this).data('value'),
            },
            options: {
                iconLayout: iconWrap,
                iconShape: {
                    type: 'Circle',
                    coordinates: [0, 0],
                    radius: 42
                },
            }
        })
    });

    objectManager.add(placemarks);

    // Добавляем цвет кластера
    objectManager.clusters.options.set({
        preset: 'islands#nightClusterIcons'
    });

    map.geoObjects.add(objectManager);
    map.setBounds(objectManager.getBounds());

    //bind click events
    objectManager.objects.events.add('click', function (e) {

        //check active mark
        let all_elem = document.getElementsByClassName('hrd__placemark active');
        if ( all_elem.length == 1 ) {
            all_elem[0].style.animation = '1s show-placemark';
            all_elem[0].classList.remove('active');
        }

        //check current elem click
        let id = e.get('objectId');
        let elem = document.getElementById(id);

        //add active class and animation
        elem.classList.add('active');
        elem.style.animation = '.35s active-placemark';

        //activate_tab
        $("a[data-placemark='#" + id +"']").trigger('click');
    });

    //следим за изменением размера карты и включаем метки в область видимости
    map.events.add('sizechange', function () {
        //вызываем функцию по оверлею
        setOverlay();
        map.setBounds(objectManager.getBounds());
    });


});

//обрабатываем клики на табы и определяем активную метку по табу
function binderPlacemark() {
    let elem = $('.placemark');

    $(elem).on('click', function () {
        $('.hrd__placemark').removeClass('active');
        let current = $(this).data('placemark');
        $(current).removeAttr('style').addClass('active');
    });

    let current = $(elem).closest('li.active').find('a').data('placemark');
    $(current).removeAttr('style').addClass('active');
}

// при смене размера добавляем оверлей, чтобы изменение размера было плавным
function setOverlay() {
    $('#map__container').addClass('overlay');
    setTimeout(function(){
        $('#map__container').removeClass('overlay');
    }, 2000);
}
