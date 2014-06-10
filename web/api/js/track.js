function trackCreate(readyCallback) {
    var container;
    var camera, scene, projector, renderer, clock;
    var cameraMap, sceneMap, rendererMap;
    var cameraLookAtPosition = new THREE.Vector3(0, 0, 0);
    var cameraUpAdd = new THREE.Vector3(0, 0, 0);
    var mouseX = 0, mouseY = 0, radius;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    var mapPadding = 100;

    var mapCarSize = 30;
    var pathSegments = 1500;
    var trackPathSplineCurve3, trackOffset=new THREE.Vector3(0,0,0), trackLength, trackLengthRealM, trackLengthRatioToReal;
    var meshMapRed, meshMapBlue;
    var meshTrack, meshCarRed, meshCarBlue;
    var carLength=50, carSegments=50, carLenSeg=carLength/carSegments;
    var carsGroup, selectedCar;
    var infoSprite;
    var spriteMaterial;
    var sideShiftPercents;

    function modPercent(percent) {
        while (percent > 1) percent--;
        while (percent < 0) percent++;
        return percent;
    }

    function modDistance(dis) {
        while (dis > trackLength) dis-=trackLength;
        while (dis < 0) dis+=trackLength;
        return dis;
    }

    function getPositionByPercent(percent) {
        percent = modPercent(percent);
        return trackPathSplineCurve3.getPointAt(percent).add(trackOffset);
    }

    function getTangentByPercent(percent) {
        percent = modPercent(percent);
        return trackPathSplineCurve3.getTangentAt(percent);
    }

    function getPositionByDistance(distance) {
        return getPositionByPercent(distance / trackLength);
    }

    function getTangentByDistance(distance) {
        return getTangentByPercent(distance / trackLength);
    }

    function setMapPosition(car) {
        car.position.copy(getPositionByDistance(car.userData.distance));
    }


    var carStepFactor = 0.1; //彩带平滑系数
    var calShiftDelta = 10; //交叉长度系数

    function clamp(val, a, b) {
        if (val <= a) return a;
        else if (val >= b) return b;
        else return val;
    }

    function calSideShift(percent) {
        var shift = 1;
        for (var i = 0; i < sideShiftPercents.length; i++) {
            if (percent >= sideShiftPercents[i]) {
                shift = (i % 2 == 0 ? 1 : -1) * Math.cos(clamp((percent - sideShiftPercents[i]) * calShiftDelta, 0, Math.PI));
            }
        }
        return shift;
    }

    function seTrackPosition(car, smooth) {
        if (smooth === undefined) smooth = true;
        for (var i = 0; i < car.geometry.vertices.length; i += 3) {
            var d = car.userData.distance - carLenSeg*i/3*(1+car.userData.faster*0.2);
            var p = getPositionByDistance(d);
            var t = getTangentByDistance(d);
            var side = t.cross(new THREE.Vector3(0, 1, 0));
            var offset = side.clone().multiplyScalar(car.userData.sideOffset * calSideShift(modPercent(d / trackLength) * 50));
            var v = car.geometry.vertices[i + 2];
            var p1 = p.clone().add(offset).add(side.clone().multiplyScalar(2));
            if (smooth) v.add(p1.sub(v).multiplyScalar(carStepFactor));
            else v.copy(p1);
            var v = car.geometry.vertices[i + 1];
            var p1 = p.clone().add(offset);
            if (smooth) v.add(p1.sub(v).multiplyScalar(carStepFactor));
            else v.copy(p1);
            var v = car.geometry.vertices[i];
            var p2 = p.clone().add(offset).add(side.negate().multiplyScalar(2));
            if (smooth) v.add(p2.sub(v).multiplyScalar(carStepFactor));
            else v.copy(p2);
        }
        car.geometry.verticesNeedUpdate = true;
        car.geometry.boundingSphere=null;
        car.geometry.boundingBox=null;

        updateInfoBox();
    }

    function setCarDistance(map, car, smooth) {
        setMapPosition(map);
        seTrackPosition(car, smooth);
    }

    function createInfoSprite(params) {
        var image=new Image();
        image.onload=function() {
            var width=image.width, height=image.height;
            var canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;
            var texture = new THREE.Texture(canvas);
            var context=canvas.getContext("2d");

            var sm=new THREE.SpriteMaterial({opacity: params.opacity, map:texture});
            sm.depthTest=false;
            var infoSprite = new THREE.Sprite(sm);
            infoSprite.updateInfo = function (car) {
                context.drawImage(image,0,height*TYPES[car.userData.typeIndex].bgOffsetV,width,height/2,0,0,width,height/2);
                if (params.drawCallback) params.drawCallback(context, car.userData);
                texture.needsUpdate = true;
                var v = car.localToWorld(car.geometry.vertices[1].clone());
                this.position.copy(v);
                this.scale.copy(infoSprite.scale0).multiplyScalar(camera.position.distanceTo(v)*params.fixedScaleFactor);
            };
            infoSprite.scale.set(params.size*(width/height), params.size, 0.1);
            infoSprite.scale0=infoSprite.scale.clone();
            infoSprite.visible = false;

            if (params.finishCallback) params.finishCallback(infoSprite);
        };
        image.src=params.imageUrl;
    }

    function updateInfoBox() {
        if (infoSprite) {
            infoSprite.visible=selectedCar!=undefined;
            if (selectedCar) {
                infoSprite.updateInfo(selectedCar);
            }
        }
    }

    (function() {
        $.ajax({ url: '/data/track.json', type: "GET", async: true, cache: false, dataType: "json", success: function (track) {
            var cameraFollowConf = track.cameraFollow, cameraFollowIndex = 0;
            sideShiftPercents = track.sideShiftPercents;
            var svgFile = track.svgFile;
            if (svgFile[0] !== '/') svgFile = 'data/' + svgFile;
            $.ajax({ url: svgFile, type: "GET", async: true, cache: false, dataType: "xml", success: function (svgDoc) {
                var pathstr = $('g path', svgDoc.documentElement).attr('d');

                dataApi.getRaceData(function(raceData){
                    if (raceData.status!=0) {
                        console.error('Data status invalid: '+raceData.message, raceData);
                    } else {
                        function init() {
                            container = document.getElementById('container');
                            projector = new THREE.Projector();
                            renderer = new THREE.WebGLRenderer({alpha: true, antialias: true});
                            renderer.setClearColor(0x000000, 0);
                            renderer.setSize(window.innerWidth, window.innerHeight);
                            container.appendChild(renderer.domElement);

                            scene = new THREE.Scene();
                            camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.5, 3000000);

                            clock = new THREE.Clock();

                            var path = transformSVGPath(pathstr);
                            var length = path.getLength();
                            var slopesVertices = [];
                            for (var i in track.slopes) {
                                var slope = track.slopes[i];
                                slopesVertices.push(new THREE.Vector3(slope.percent / 100 * length, slope.offset * track.slopeScale, 0));
                            }
                            slopePathSplineCurve3 = new THREE.ClosedSplineCurve3(slopesVertices);
                            var vectices2D = path.getSpacedPoints(pathSegments, true);
                            var vertices = [];
                            for (var i = 0; i < vectices2D.length; i++) {
                                vertices.push(new THREE.Vector3(vectices2D[i].x, slopePathSplineCurve3.getPointAt(i / vectices2D.length).y/*?*/, vectices2D[i].y));
                            }

                            trackPathSplineCurve3 = new THREE.ClosedSplineCurve3(vertices);
                            trackLength = trackPathSplineCurve3.getLength();

                            trackLengthRealM = track.trackLengthKM * 1000;
                            trackLengthRatioToReal = trackLengthRealM / trackLength;

                            var trackGeometry = new THREE.PlaneGeometry(5 + 2, trackLength, 1, pathSegments);
                            var trackLenSeg = trackLength / pathSegments;
                            for (var i = 0; i < trackGeometry.vertices.length; i += 2) {
                                var d = trackLenSeg * i / 2;
                                var p = getPositionByDistance(d);
                                var t = getTangentByDistance(d);
                                var side = t.cross(new THREE.Vector3(0, 1, 0));
                                var offset = side.clone().multiplyScalar(3);
                                var v = trackGeometry.vertices[i + 1];
                                v.addVectors(p, offset);
                                var v = trackGeometry.vertices[i];
                                v.addVectors(p, offset.negate());
                            }
                            trackGeometry.verticesNeedUpdate = true;
                            trackGeometry.computeBoundingSphere();
                            trackGeometry.computeBoundingBox();
                            trackOffset = trackGeometry.boundingSphere.center.clone().negate();

                            var redCarData = {name: raceData.data.weibo.name, distance: raceData.data.weibo.distance,
                                rankings: raceData.data.weibo.rankings, lap: raceData.data.weibo.lap,
                                speed: raceData.data.weibo.speed * 1000 / 60 / 60 / trackLengthRatioToReal,
                                sideOffset: 2.5, typeIndex: raceData.data.weibo.typeIndex, faster: 0};
                            redCarData.distance1 = modDistance(redCarData.distance);
                            redCarData.lap0 = redCarData.lap;
                            var blueCarData = {name: raceData.data.twitter.name, distance: raceData.data.twitter.distance,
                                rankings: raceData.data.twitter.rankings, lap: raceData.data.twitter.lap,
                                speed: raceData.data.twitter.speed * 1000 / 60 / 60 / trackLengthRatioToReal,
                                sideOffset: -2.5, typeIndex: raceData.data.twitter.typeIndex, faster: 0};
                            blueCarData.distance1 = modDistance(blueCarData.distance);
                            blueCarData.lap0 = blueCarData.lap;

                            sceneMap = new THREE.Scene();
                            var mapGeometry = new THREE.PlaneGeometry(5 + 2, trackLength, 1, pathSegments);
                            var trackLenSeg = trackLength / pathSegments;
                            for (var i = 0; i < mapGeometry.vertices.length; i += 2) {
                                var d = trackLenSeg * i / 2;
                                var p = getPositionByDistance(d);
                                var t = getTangentByDistance(d);
                                var side = t.cross(new THREE.Vector3(0, 3, 0));
                                var offset = side.clone().multiplyScalar(3);
                                var v = mapGeometry.vertices[i + 1];
                                v.addVectors(p, offset);
                                var v = mapGeometry.vertices[i];
                                v.addVectors(p, offset.negate());
                            }
                            mapGeometry.verticesNeedUpdate = true;
                            mapGeometry.computeBoundingSphere();
                            mapGeometry.computeBoundingBox();
                            var mapOffset = mapGeometry.boundingSphere.center.clone().negate();
                            meshMap = new THREE.Mesh(
                                mapGeometry,
                                new THREE.MeshBasicMaterial({color: 0xffffff, side: THREE.DoubleSide })
                            );
                            var bb = mapGeometry.boundingBox;
                            var x0 = bb.max.x - bb.min.x + mapPadding * 2;
                            var z0 = bb.max.z - bb.min.z + mapPadding * 2;
                            var y0 = bb.max.y - bb.min.y + mapPadding * 2;
                            var w = 200, h = z0 / x0 * w;
                            meshMap.position.add(mapOffset);
                            meshMap.matrixAutoUpdate = false;
                            meshMap.updateMatrix();
                            sceneMap.add(meshMap);
                            map = document.getElementById('map');
                            rendererMap = new THREE.WebGLRenderer({alpha: true, antialias: true});
                            rendererMap.setClearColor(0x000000, 0);
                            rendererMap.setSize(w, h);
                            map.appendChild(rendererMap.domElement);
                            cameraMap = new THREE.OrthographicCamera(x0 / -2, x0 / 2, z0 / 2, z0 / -2, -500, 2000);
                            cameraMap.position.set(0, 1000, 0);
                            cameraMap.up.set(0, 0, -1);
                            cameraMap.lookAt(new THREE.Vector3(0, 0, 0));

                            meshMapRed = new THREE.Mesh(new THREE.SphereGeometry(mapCarSize), new THREE.MeshBasicMaterial({color: 0xff0000}));
                            meshMapRed.position.set(0, 100, 0);
                            meshMapRed.userData = redCarData;
                            sceneMap.add(meshMapRed);

                            meshMapBlue = new THREE.Mesh(new THREE.SphereGeometry(mapCarSize), new THREE.MeshBasicMaterial({color: 0x0000ff}));
                            meshMapBlue.position.set(0, 100, 0);
                            meshMapBlue.userData = blueCarData;
                            sceneMap.add(meshMapBlue);

                            var trackTexture = THREE.ImageUtils.loadTexture('/image/track.png?' + (new Date()).getTime());
                            trackTexture.wrapS = trackTexture.wrapT = THREE.RepeatWrapping;
                            trackTexture.repeat.set(1, 50);

                            meshTrack = new THREE.Mesh(
                                trackGeometry,
                                new THREE.MeshBasicMaterial({
                                    transparent: true, opacity: 1, side: THREE.DoubleSide,
                                    map: trackTexture
                                })
                            );
                            meshTrack.geometry.computeBoundingSphere();
                            meshTrack.position.add(trackOffset);
                            meshTrack.matrixAutoUpdate = false;
                            meshTrack.updateMatrix();
                            scene.add(meshTrack);

                            radius = meshTrack.geometry.boundingSphere.radius;

                            var circleGeometry1 = new THREE.CircleGeometry(radius * 3, 50);
                            circleGeometry1.applyMatrix(new THREE.Matrix4().makeRotationX(-Math.PI / 2));
                            circleGeometry1.vertices.shift();
                            var meshCircle1 = new THREE.Line(circleGeometry1, new THREE.LineBasicMaterial({ color: 0x999999 }));
                            meshCircle1.position.add(trackOffset);
                            meshCircle1.matrixAutoUpdate = false;
                            meshCircle1.updateMatrix();
                            scene.add(meshCircle1);
                            var circleGeometry2 = circleGeometry1.clone();
                            circleGeometry2.applyMatrix(new THREE.Matrix4().makeTranslation(0, 20, 0));
                            var meshCircle2 = new THREE.Line(circleGeometry2, new THREE.LineBasicMaterial({ color: 0xffffff }));
                            meshCircle2.position.add(trackOffset);
                            meshCircle2.matrixAutoUpdate = false;
                            meshCircle2.updateMatrix();
                            scene.add(meshCircle2);

                            carsGroup = new THREE.Object3D;
                            scene.add(carsGroup);

                            var geometry = new THREE.PlaneGeometry(5, carLength, 2, carSegments);
                            meshCarRed = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({transparent: true, opacity: 1, side: THREE.DoubleSide, alphaTest: 0.5,
                                map: THREE.ImageUtils.loadTexture('/image/car-red.png?' + (new Date()).getTime())}));
                            meshCarRed.frustumCulled = false;
                            meshCarRed.position.set(0, 1, 0);
                            meshCarRed.matrixAutoUpdate = false;
                            meshCarRed.updateMatrix();
                            meshCarRed.userData = redCarData;
                            setCarDistance(meshMapRed, meshCarRed, false);
                            carsGroup.add(meshCarRed);

                            var geometry = new THREE.PlaneGeometry(5, carLength, 2, carSegments);
                            meshCarBlue = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({transparent: true, opacity: 1, side: THREE.DoubleSide, alphaTest: 0.5,
                                map: THREE.ImageUtils.loadTexture('/image/car-blue.png?' + (new Date()).getTime())}));
                            meshCarBlue.frustumCulled = false;
                            meshCarBlue.position.set(0, 1.2, 0);
                            meshCarBlue.userData = blueCarData;
                            setCarDistance(meshMapBlue, meshCarBlue, false);
                            carsGroup.add(meshCarBlue);

                            createInfoSprite({ imageUrl: '/image/track-infobox.png?' + (new Date()).getTime(), opacity: 0.86, size: 15, fixedScaleFactor: 0.015,
                                finishCallback: function (sprite) {
                                    infoSprite = sprite;
                                    scene.add(infoSprite);
                                },
                                drawCallback: function (context, data) {
                                    var padding = 10;
                                    context.fillStyle = TYPES[data.typeIndex].color;
                                    context.textBaseline = 'top';
                                    context.font = "28pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText(data.name, 12, 8);
                                    context.font = "42pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText("" + data.rankings, 40, 50);
                                    context.font = "26pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText(rankingsTitle(data.rankings), 72, 50);
                                    context.font = "15pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText('' + Math.round(data.speed * trackLengthRatioToReal * 60 * 60 / 1000) + 'km/h', 125, 58);
                                    context.font = "14pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText("Lap:" + Math.round(data.lap), 125, 84);
                                }
                            });

                            var canvas = document.createElement("canvas");
                            canvas.width = canvas.height = 20;
                            var texture = new THREE.Texture(canvas);
                            var context = canvas.getContext("2d");
                            var s = 20, w = 1;
                            context.lineWidth = w;
                            context.lineJoin = "miter";
                            context.strokeStyle = 'white';
                            context.beginPath();
                            context.moveTo(w, w);
                            context.lineTo(s - w, w);
                            context.lineTo(s - w, s - w);
                            context.lineTo(w, s - w);
                            context.closePath();
                            context.stroke();
                            texture.needsUpdate = true;

                            spriteMaterial = new THREE.SpriteMaterial({ opacity: 0.6, map: texture });
                            for (var i = 0; i < track.particle.count; i++) {
                                var particle = new THREE.Sprite(spriteMaterial);
                                particle.position.copy(getPositionByPercent(i / track.particle.count)).add(new THREE.Vector3((Math.random() - 0.5) * 100, (Math.random() - 0.5) * 10, (Math.random() - 0.5) * 100));
                                particle.scale.multiplyScalar(0.5 + Math.random() * 0.3);
                                scene.add(particle);
                            }

                            camera.position.set(cameraFollowConf.initPosition.x, cameraFollowConf.initPosition.y, cameraFollowConf.initPosition.z);
                            camera.lookAt(getPositionByPercent(0));

                            window.addEventListener('resize', onWindowResize, false);
                            document.addEventListener('mousemove', onDocumentMouseMove, false);
                            document.addEventListener('click', onDocumentClick, false);

                            animate();

                            if (readyCallback) readyCallback();
                        }

                        function onWindowResize() {
                            windowHalfX = window.innerWidth / 2;
                            windowHalfY = window.innerHeight / 2;
                            camera.aspect = window.innerWidth / window.innerHeight;
                            camera.updateProjectionMatrix();

                            renderer.setSize(window.innerWidth, window.innerHeight);

                        }

                        function focus(event) {
                            if (typeof event.offsetX === "undefined" || typeof event.offsetY === "undefined") {
                                var targetOffset = $(event.target).offset();
                                event.offsetX = event.pageX - targetOffset.left;
                                event.offsetY = event.pageY - targetOffset.top;
                            }
                            var vector = new THREE.Vector3(( event.offsetX / window.innerWidth ) * 2 - 1, -( event.offsetY / window.innerHeight ) * 2 + 1, 0.5);
                            projector.unprojectVector(vector, camera);
                            var ray = new THREE.Raycaster(camera.position, vector.sub(camera.position).normalize());
                            var intersects = ray.intersectObjects(carsGroup.children);
                            var selectedCar1;
                            if (intersects.length > 0) selectedCar1 = intersects[0].object;
                            if (selectedCar1 != selectedCar) {
                                selectedCar = selectedCar1;
                                updateInfoBox();

                                //render();
                            }
                        }

                        function onDocumentMouseMove(event) {

                            mouseX = ( event.clientX - windowHalfX );
                            mouseY = -( event.clientY - windowHalfY );

                            focus(event);
                        }

                        function onDocumentClick(event) {
                            cameraFollowIndex = (cameraFollowIndex + 1) % cameraFollowConf.locations.length;
                        }

                        function cameraFollowPosition(targetDistance) {
                            var cameraLocation = cameraFollowConf.locations[cameraFollowIndex];
                            var cameraPosition = getPositionByDistance(targetDistance + cameraLocation.distance);
                            if (cameraLocation.height) cameraPosition.y += cameraLocation.height;
                            return cameraPosition;
                        }

                        function cameraFollow(distance) {
                            var cameraLookTarget = getPositionByDistance(distance).add(cameraUpAdd);
                            cameraLookAtPosition.add(cameraLookTarget.sub(cameraLookAtPosition).multiplyScalar(cameraFollowConf.lookAtFactor));
                            var cameraPos = cameraFollowPosition(distance);
                            camera.position.add(cameraPos.sub(camera.position).multiplyScalar(cameraFollowConf.followFactor));
                            camera.lookAt(cameraLookAtPosition);
                            var up = new THREE.Vector3(mouseX / windowHalfX, 1, 0);
                            camera.up.add(up.sub(camera.up).multiplyScalar(0.05));
                        }

                        function animate() {
                            requestAnimationFrame(animate);
                            var delta = clock.getDelta();
                            meshCarRed.userData.distance += delta * meshCarRed.userData.speed;
                            meshCarRed.userData.distance1 += delta * meshCarRed.userData.speed;
                            meshCarRed.userData.lap = meshCarRed.userData.lap0 + Math.floor(meshCarRed.userData.distance1 / trackLength);
                            meshCarBlue.userData.distance += delta * meshCarBlue.userData.speed;
                            meshCarBlue.userData.distance1 += delta * meshCarBlue.userData.speed;
                            meshCarBlue.userData.lap = meshCarBlue.userData.lap0 + Math.floor(meshCarBlue.userData.distance1 / trackLength);
                            setCarDistance(meshMapRed, meshCarRed);
                            setCarDistance(meshMapBlue, meshCarBlue);
                            var followDistance;
                            if (Math.max(meshCarRed.userData.distance > meshCarBlue.userData.distance)) {
                                followDistance = meshCarRed.userData.distance;
                                meshCarRed.userData.rankings = 1;
                                meshCarBlue.userData.rankings = 2;
                            } else {
                                followDistance = meshCarBlue.userData.distance;
                                meshCarBlue.userData.rankings = 1;
                                meshCarRed.userData.rankings = 2;
                            }
                            if (Math.max(meshCarRed.userData.speed > meshCarBlue.userData.speed)) {
                                meshCarRed.userData.faster = 1;
                                meshCarBlue.userData.faster = 0;
                            } else {
                                meshCarRed.userData.faster = 0;
                                meshCarBlue.userData.faster = 1;
                            }
                            cameraFollow(followDistance);
                            spriteMaterial.rotation = (-clock.getElapsedTime() * Math.PI * 1.5);

                            render();
                        }

                        function render() {
                            renderer.render(scene, camera);
                            rendererMap.render(sceneMap, cameraMap);
                        }

                        init();
                    }
                });
            }});
        }});
    })();
}