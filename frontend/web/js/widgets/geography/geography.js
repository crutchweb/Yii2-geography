var map;

ymaps.ready(function () {

    var mapselector = $('#map__container');

    //render ya map
    window.map = new ymaps.Map('map__container', {
            center: [58.525798, 90.516063],
            zoom: 3,
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
        '<img src="/images/static/sprites/hrd_placemark.svg">' +
        '</div>',
        {
            build: function () {
                iconWrap.superclass.build.call(this);

                objectManager.objects.options.set({
                    //hasBalloon: false,
                    hideIconOnBalloonOpen: false
                });

                let elem = document.getElementById(this._data.id);
                //show animation
                elem.style.animation = '1s show-placemark';

            },
            clear: function () {
                iconWrap.superclass.clear.call(this);
            },
        }
    );

    var baloonWrap = ymaps.templateLayoutFactory.createClass(
        '<div class="placemark__popover">' +
        '<a class="close" href="#">&times;</a>' +
        '<div class="title">Город: {{properties.balloonContentHeader}}</div>' +
        '<div class="title">Адрес: {{properties.balloonContentBody}}</div>' +
        '<ul class="additional__info load"></ul>' +
        '<div class="action_block">{{properties.balloonContentFooter|raw}}</div>' +
        '</div>',
        {
            build: function () {
                this.constructor.superclass.build.call(this);

                this._$element = $('.placemark__popover', this.getParentElement());

                this.applyElementOffset();

                this._$element.find('.close')
                    .on('click', $.proxy(this.onCloseClick, this));
            },
            clear: function () {
                this.constructor.superclass.clear.call(this);
                this._$element.find('.close')
                    .off('click');
            },
            onSublayoutSizeChange: function () {
                this.superclass.onSublayoutSizeChange.apply(this, arguments);

                if(!this._isElement(this._$element)) {
                    return;
                }

                this.applyElementOffset();

                this.events.fire('shapechange');
            },
            applyElementOffset: function () {
                this._$element.css({
                    left: -(this._$element[0].offsetWidth / 2) + 12,
                    bottom: -(this._$element[0].offsetHeight - 150),
                });
            },
            onCloseClick: function (e) {
                e.preventDefault();

                this.events.fire('userclose');
            },
            getShape: function () {
                if(!this._isElement(this._$element)) {
                    return baloonWrap.superclass.getShape.call(this);
                }

                var position = this._$element.position();

                return new ymaps.shape.Rectangle(new ymaps.geometry.pixel.Rectangle([
                    [position.left, position.top], [
                        position.left + this._$element[0].offsetWidth,
                        position.top + this._$element[0].offsetHeight + this._$element.find('.arrow')[0].offsetHeight
                    ]
                ]));
            },
            _isElement: function (element) {
                return element && element[0] && element.find('.arrow')[0];
            }
        }
    );

    map.geoObjects.add(objectManager);

    //загружаем все филиалы и формируем кластеры (без телефонов и почты)
    $.ajax({
        type: "POST",
        url: "/geography/get-placemark"
    }).done(function (data) {
        objectManager.add(data);
        objectManager.clusters.options.set({
            preset: 'islands#nightClusterIcons',
            pane: 'overlaps'
        });
        objectManager.objects.options.set({
            iconLayout: iconWrap,
            balloonLayout: baloonWrap,
            iconShape: {
                type: "Circle",
                coordinates: [0, 0],
                radius: 26,
                zIndexActive: 9999
            },
            hideIconOnBalloonOpen: false,
            panelMaxMapArea: 0
        });

        map.setBounds(objectManager.getBounds());
    });

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

        //загружает дополнительную информацию по клику на метке
        $.ajax({
            type: "GET",
            data: "id=" + id,
            url: "/geography/get-branch-data"
        }).done(function (data) {
            let list = document.getElementsByClassName('additional__info');
            for (var i in data) {
                // обработка пустого значения "comment"
                if (data[i].type == 'phone'){
                    var text = 'Телефон для справок: ';
                }else if (data[i].type == 'mail'){
                    var text = 'Email для справок: ';
                }

                if (data[i].comment) {
                    var text = data[i].comment + ': ';
                }

                //создаем селектор и апендим в него данные
                let li = document.createElement('li');
                list[0].append(text + data[i].value, li);
                list[0].classList.remove('load'); //удаялем прелоадер на загрузку
            }
        });
        //add active class and animation
        elem.classList.add('active');
        elem.style.animation = '.35s active-placemark';
    });
});