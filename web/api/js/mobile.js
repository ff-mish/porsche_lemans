function trackCreate(readyCallback) {
    var container;
    var camera, scene, projector, renderer, clock;
    var trackWidth, trackHeight, trackPadding=100;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;
    var pathSegments = 1000;
    var trackPathSplineCurve3, trackOffset=new THREE.Vector3(0,0,0), trackLength, trackLengthRealM, trackLengthRatioToReal;
    var meshTrack, meshCarRed, meshCarBlue;
    var carLength=50, carSegments=50, carLenSeg=carLength/carSegments;
    var carsGroup, selectedCar;
    var infoSprite;

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


    var carStepFactor = 0.1; //彩带平滑系数

    function clamp(val, a, b) {
        if (val <= a) return a;
        else if (val >= b) return b;
        else return val;
    }

    function calInfoBoxOffsetIndex(car) {
        var a=Math.atan2(car.side.x,car.side.z);
        return  Math.floor((a+Math.PI/4)/(Math.PI/2)+4)%4;
    }

    function seTrackPosition(car, smooth) {
        if (smooth === undefined) smooth = true;
        for (var i = 0; i < car.geometry.vertices.length; i += 3) {
            var d = car.userData.distance - carLenSeg*i/3*(5+car.userData.faster*0.2);
            var p = getPositionByDistance(d);
            var t = getTangentByDistance(d);
            var side = t.cross(new THREE.Vector3(0, 10, 0));
            //if (i==(carSegments-10)*3+1+car.userData.infoBoxSide) car.side=side.clone().multiplyScalar(car.userData.infoBoxSide);
            if (i==0) car.side=side.clone().multiplyScalar(car.userData.infoBoxSide);
            var offset = side.clone().multiplyScalar(car.userData.sideOffset*1.6);
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

    function setCarDistance(car, smooth) {
        seTrackPosition(car, smooth);
    }

    var spriteOffset=[
        {wo:1, x:1, y:0.5},    //left
        {wo:0, x:0.5, y:0},    //down
        {wo:3, x:0, y:0.5},     //right
        {wo:2, x:0.5, y:1}      //up
    ];

    function createInfoSprite(params) {
        var image=new Image();
        image.onload=function() {
            var width=image.width/4, height=image.height/2;
            var canvas = document.createElement("canvas");
            canvas.width = width*2;
            canvas.height = height*2;
            var texture = new THREE.Texture(canvas);
            var context=canvas.getContext("2d");

            var sm=new THREE.SpriteMaterial({opacity: params.opacity, map:texture});
            sm.depthTest=false;
            var infoSprite = new THREE.Sprite(sm);
            infoSprite.updateInfo = function (car) {
                var so=spriteOffset[calInfoBoxOffsetIndex(car)];
                var padding=13;
                var offsetX=width*so.x, offsetY=height*so.y;
                context.clearRect(0,0,canvas.width,canvas.height);
                context.drawImage(image,width*so.wo,height*TYPES[car.userData.typeIndex].bgOffsetV,width,height,offsetX,offsetY,width,height);
                if (params.drawCallback) params.drawCallback(context, offsetX+padding, offsetY+padding, car.userData);
                texture.needsUpdate = true;
                var v = car.localToWorld(car.geometry.vertices[(carSegments-10)*3+1+car.userData.infoBoxSide].clone().setY(100));
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
        $.ajax({ url: 'data/mobile.json', type: "GET", async: true, cache: false, dataType: "json", success: function (track) {
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
                            renderer = new THREE.CanvasRenderer({alpha: true});
                            renderer.setClearColor(0x000000, 0);
                            renderer.setSize(window.innerWidth, window.innerHeight);
                            container.appendChild(renderer.domElement);

                            scene = new THREE.Scene();

                            clock = new THREE.Clock();

                            var path = transformSVGPath(pathstr);
                            var vectices2D = path.getSpacedPoints(pathSegments, true);
                            var vertices = [];
                            for (var i = 0; i < vectices2D.length; i++) {
                                vertices.push(new THREE.Vector3(vectices2D[i].x, 0, vectices2D[i].y));
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
                                var side = t.cross(new THREE.Vector3(0, track.trackWidth, 0));
                                var offset = side.clone().multiplyScalar(3);
                                var v = trackGeometry.vertices[i + 1];
                                v.addVectors(p, offset);
                                var v = trackGeometry.vertices[i];
                                v.addVectors(p, offset.negate());
                            }
                            trackGeometry.verticesNeedUpdate = true;
                            trackGeometry.applyMatrix(new THREE.Matrix4().makeRotationY(track.rotate/180*Math.PI));
                            trackGeometry.computeBoundingSphere();
                            trackGeometry.computeBoundingBox();

                            var redCarData = {name: raceData.data.weibo.name, distance: raceData.data.weibo.distance,
                                rankings: raceData.data.weibo.rankings, lap: raceData.data.weibo.lap,
                                sideOffset: 2.5, infoBoxSide:1, typeIndex: raceData.data.weibo.typeIndex};
                            redCarData.speed0=raceData.data.weibo.speed;
                            redCarData.speed=(raceData.data.weibo.speed<100?100:raceData.data.weibo.speed) * 1000 / 60 / 60 / trackLengthRatioToReal;
                            redCarData.distance1 = modDistance(redCarData.distance);
                            redCarData.lap0 = redCarData.lap;
                            var blueCarData = {name: raceData.data.twitter.name, distance: raceData.data.twitter.distance,
                                rankings: raceData.data.twitter.rankings, lap: raceData.data.twitter.lap,
                                sideOffset: -2.5, infoBoxSide:-1, typeIndex: raceData.data.twitter.typeIndex};
                            blueCarData.speed0=raceData.data.twitter.speed;
                            blueCarData.speed=(raceData.data.twitter.speed<100?100:raceData.data.twitter.speed) * 1000 / 60 / 60 / trackLengthRatioToReal;
                            blueCarData.distance1 = modDistance(blueCarData.distance);
                            blueCarData.lap0 = blueCarData.lap;

                            if (Math.max(redCarData.speed0 > blueCarData.speed0)) {
                                redCarData.faster = 1;
                                blueCarData.faster = 0;
                            } else {
                                redCarData.faster = 0;
                                blueCarData.faster = 1;
                            }

                            var trackBoundingBox=trackGeometry.boundingBox;
                            trackWidth=trackBoundingBox.max.x-trackBoundingBox.min.x;
                            trackHeight=trackBoundingBox.max.z-trackBoundingBox.min.z;

                            trackGeometry.applyMatrix(new THREE.Matrix4().makeRotationY(-track.rotate/180*Math.PI));
                            meshTrack = new THREE.Mesh( trackGeometry, new THREE.MeshBasicMaterial({side: THREE.BackSide, overdraw:0.5}));
                            meshTrack.geometry.computeBoundingBox();
                            meshTrack.geometry.computeBoundingSphere();
                            trackOffset = trackGeometry.boundingSphere.center.clone().negate();
                            meshTrack.position.add(trackOffset);
                            //meshTrack.rotation.y=track.rotate/180*Math.PI;//
                            meshTrack.matrixAutoUpdate = false;
                            meshTrack.updateMatrix();
                            scene.add(meshTrack);

                            var w=window.innerWidth-trackPadding* 2, h=window.innerHeight-trackPadding*2;
                            var d=Math.min(w/trackWidth, h/trackHeight);
                            camera = new THREE.OrthographicCamera(w/d/-2-trackPadding/d, w/d/2+trackPadding/d, h/d/2+trackPadding/d, h/d/-2-trackPadding/d, -500, 2000);
                            camera.position.set(0, 1000, 0);
                            camera.up.set(1.42,1,-1).normalize();
                            camera.lookAt(new THREE.Vector3(0, 0, 0));

                            carsGroup = new THREE.Object3D;
                            scene.add(carsGroup);

                            var geometry = new THREE.PlaneGeometry(5, carLength, 2, carSegments);
                            meshCarRed = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({transparent: true, opacity: 1, side: THREE.DoubleSide, alphaTest: 0.5, overdraw:0.5,
                                color:TYPES[0].color}));
                            meshCarRed.frustumCulled = false;
                            meshCarRed.position.set(0, 1, 0);
                            meshCarRed.matrixAutoUpdate = false;
                            meshCarRed.updateMatrix();
                            meshCarRed.userData = redCarData;
                            setCarDistance(meshCarRed, false);
                            carsGroup.add(meshCarRed);

                            var geometry = new THREE.PlaneGeometry(5, carLength, 2, carSegments);
                            meshCarBlue = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({transparent: true, opacity: 1, side: THREE.DoubleSide, alphaTest: 0.5, overdraw:0.5,
                                color:TYPES[1].color}));
                            meshCarBlue.frustumCulled = false;
                            meshCarBlue.position.set(0, 1.2, 0);
                            meshCarBlue.userData = blueCarData;
                            setCarDistance(meshCarBlue, false);
                            carsGroup.add(meshCarBlue);

                            createInfoSprite({ imageUrl: 'image/mobile-infobox.png?' + (new Date()).getTime(), opacity: 0.86, size: 15, fixedScaleFactor: 0.02,
                                finishCallback: function (sprite) {
                                    infoSprite = sprite;
                                    scene.add(infoSprite);
                                },
                                drawCallback: function (context, offsetX, offsetY, data) {
                                    context.fillStyle = TYPES[data.typeIndex].color;
                                    context.textBaseline = 'top';
                                    context.font = "28pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText(data.name, offsetX+12, offsetY+8);
                                    context.font = "42pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText("" + data.rankings, offsetX+40, offsetY+50);
                                    context.font = "26pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText(rankingsTitle(data.rankings), offsetX+72, offsetY+50);
                                    context.font = "15pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText('' + Math.round(data.speed0) + 'km/h', offsetX+125, offsetY+58);
                                    context.font = "14pt 'Porsche News Gothic','黑体','sans-serif'";
                                    context.fillText("Lap:" + Math.round(data.lap), offsetX+125, offsetY+84);
                                }
                            });

                            window.addEventListener('resize', onWindowResize, false);
                            document.addEventListener('touchend', onDocumentClick, false);

                            animate();

                            if (readyCallback) readyCallback();
                        }

                        function onWindowResize() {
                            windowHalfX = window.innerWidth / 2;
                            windowHalfY = window.innerHeight / 2;

                            var w=window.innerWidth-trackPadding* 2, h=window.innerHeight-trackPadding*2;
                            var d=Math.min(w/trackWidth, h/trackHeight);
                            camera.left=w/d/-2-trackPadding/d;
                            camera.right=w/d/2+trackPadding/d;
                            camera.top=h/d/2+trackPadding/d;
                            camera.bottom=h/d/-2-trackPadding/d;
                            camera.updateProjectionMatrix();

                            renderer.setSize(window.innerWidth, window.innerHeight);

                        }

                        function focus(event) {
                            selectedCar=selectedCar==meshCarRed?meshCarBlue:meshCarRed;
                            updateInfoBox();
                            render();

                            return;
                            /*
                            var vector = new THREE.Vector3(( event.clientX / window.innerWidth ) * 2 - 1, -( event.clientY / window.innerHeight ) * 2 + 1, 0.5);
                            var ray=projector.pickingRay(vector, camera);
                            var intersects = ray.intersectObjects(carsGroup.children);
                            var selectedCar1;
                            if (intersects.length > 0) selectedCar1 = intersects[0].object;
                            if (selectedCar1 != selectedCar) {
                                selectedCar = selectedCar1;
                                updateInfoBox();

                                //render();
                            }*/
                        }

                        function onDocumentClick(event) {
                            focus(event);
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
                            setCarDistance(meshCarRed);
                            setCarDistance(meshCarBlue);
                            if (Math.max(meshCarRed.userData.distance > meshCarBlue.userData.distance)) {
                                followDistance = meshCarRed.userData.distance;
                                meshCarRed.userData.rankings = 1;
                                meshCarBlue.userData.rankings = 2;
                            } else {
                                followDistance = meshCarBlue.userData.distance;
                                meshCarBlue.userData.rankings = 1;
                                meshCarRed.userData.rankings = 2;
                            }

                            render();
                        }

                        function render() {
                            renderer.render(scene, camera);
                        }

                        init();
                    }
                });
            }});
        }});
    })();
}