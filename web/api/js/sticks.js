function sticksCreate(readyCallback) {
    var stickMaxHeight = 400, stickWidth = 3, stickPadding = 0.4, fillAniFinished = false;
    var container;
    var camera, scene, projector, renderer;

    var mouseX = 0, mouseY = 0;
    var scrollSpeed = 0, scrollSpeedMax = 1500, scrollSpeedMin = 0.01, scrollSpeedStopDelta = 0.95, scrollSpeedAcc = 0;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    var infoSprite;
    var fillPercentValue;
    var sticksGroup, sticksWidth, selectedStick;

    function barHeightCal(distance) {
        var ret = 0;
        if (distance / 5<=1) ret += Math.sin(distance / 5 * Math.PI) * 3;
        if ((distance / 5>=0.8))  ret += (distance - 4) * (distance - 4) / 5;
        return ret;
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

            var infoSprite = new THREE.Sprite(new THREE.SpriteMaterial({opacity: params.opacity, map:texture}));
            infoSprite.updateInfo = function (stick) {
                context.drawImage(image,0,height*TYPES[stick.userData.typeIndex].bgOffsetV,width,height/2,0,0,width,height/2);
                if (params.drawCallback) params.drawCallback(context, stick.userData);
                texture.needsUpdate = true;
                var v = stick.localToWorld(stick.geometry.vertices[1].clone());
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
            infoSprite.visible=selectedStick!=undefined;
            if (selectedStick) {
                infoSprite.updateInfo(selectedStick);
            }
        }
    }

    (function () {
        $.ajax({ url: 'shader/vertex.glessl', type: "GET", async: true, cache: false, dataType: "text", success: function (vertexShader) {
            $.ajax({ url: 'shader/fragment.glessl', type: "GET", async: true, cache: false, dataType: "text", success: function (fragmentShader) {
                function init() {
                    container = document.getElementById('container');
                    projector = new THREE.Projector();
                    renderer = new THREE.WebGLRenderer({alpha: true, antialias: true});
                    renderer.setClearColor(0x000000, 0);
                    renderer.setSize(window.innerWidth, window.innerHeight);
                    container.appendChild(renderer.domElement);

                    scene = new THREE.Scene();
                    camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.5, 3000000);

                    createInfoSprite({ imageUrl:'image/sticks-infobox.png?'+(new Date()).getTime(), opacity:0.86, size:15, fixedScaleFactor:0.015,
                        finishCallback:function(sprite) {
                            infoSprite=sprite;
                            scene.add(infoSprite);
                        },
                        drawCallback: function(context, data) {
                            context.fillStyle = TYPES[data.typeIndex].color;
                            context.textBaseline = 'top';
                            context.font="30px 'Porsche News Gothic','黑体','sans-serif'";
                            context.fillText('P'+data.rankings+'/'+Math.round(data.teamSize), 21, 16);
                            context.font="19.65px 'Porsche News Gothic','黑体','sans-serif'";
                            context.fillText(data.team, 23, 60);
                            context.fillText(''+Math.round(data.speed) + 'km/h', 132, 60);
                            context.fillText("View Profi", 23, 87);
                            context.fillText("Lap:" + data.lap, 132, 87);
                        }
                    });

                    fillPercentValue = { type: "f", value: 0 };
                    var gradientLength = { type: "f", value: 100.0 };
                    var gradientStartOpacity = { type: "f", value: 0.6 };
                    var normalStickMaterialUniforms = {
                        fillPercent: fillPercentValue,
                        color: { type: "c", value: new THREE.Color(0xcccccc) },
                        opacity: { type: "f", value: 1.0 },
                        gradientLength: gradientLength,
                        gradientStartOpacity: gradientStartOpacity
                    };
                    var selectedStickMaterialUniforms =[
                        {
                            fillPercent: fillPercentValue,
                            color: { type: "c", value: new THREE.Color(TYPES[0].color) },
                            opacity: { type: "f", value: 1.0 },
                            gradientLength: gradientLength,
                            gradientStartOpacity: gradientStartOpacity
                        },{
                            fillPercent: fillPercentValue,
                            color: { type: "c", value: new THREE.Color(TYPES[1].color) },
                            opacity: { type: "f", value: 1.0 },
                            gradientLength: gradientLength,
                            gradientStartOpacity: gradientStartOpacity
                        }
                    ];

                    sticksGroup = new THREE.Object3D;
                    scene.add(sticksGroup);

                    dataApi.getTeamData(function (teamData) {
                        if (teamData.status!=0) {
                            console.error('Data status invalid: '+teamData.message, teamData);
                        } else {
                            var stickCount = teamData.data.teams.length, perDistance=stickMaxHeight/stickCount;
                            var teamSizes=[teamData.data.weibo_total, teamData.data.twitter_total];
                            for (var i = 0; i < stickCount; i++) {
                                var stickData = teamData.data.teams[i];
                                stickData.teamSize=teamSizes[stickData.typeIndex];
                                var h=i==0?stickMaxHeight:Math.round((perDistance*(stickCount-i+(Math.random()-0.5)*0.9))*100)/100;
                                var geometry = new THREE.PlaneGeometry(stickWidth, h, 2, Math.ceil(50 / 400 * h));
                                geometry.applyMatrix(new THREE.Matrix4().makeRotationX(-Math.PI / 2));
                                geometry.applyMatrix(new THREE.Matrix4().makeTranslation((stickWidth + stickPadding) * i, 0, -h / 2));
                                for (var j = 0; j < geometry.vertices.length; j += 3) {
                                    var v = geometry.vertices.length - j;
                                    var y = barHeightCal(j / geometry.vertices.length / 15 * h);
                                    geometry.vertices[v - 3].y = y;
                                    geometry.vertices[v - 2].y = y;
                                    geometry.vertices[v - 1].y = y;
                                }
                                geometry.dynamic = false;

                                var attributes = {
                                    percent: {
                                        type: 'f',
                                        value: []
                                    }
                                };
                                var verts = geometry.vertices;
                                var values = attributes.percent.value;
                                for (var v = 0; v < verts.length / 3; v++) {
                                    var p = 100 - v / (verts.length / 3 - 1) * 100;
                                    values.push(p, p, p);
                                }

                                var normalStickMaterial = new THREE.ShaderMaterial({
                                    uniforms: normalStickMaterialUniforms,
                                    attributes: attributes,
                                    vertexShader: vertexShader,
                                    fragmentShader: fragmentShader,
                                    side: THREE.DoubleSide,
                                    transparent: true
                                });
                                var selectedStickMaterial = [
                                    new THREE.ShaderMaterial({
                                        uniforms: selectedStickMaterialUniforms[0],
                                        attributes: attributes,
                                        vertexShader: vertexShader,
                                        fragmentShader: fragmentShader,
                                        side: THREE.DoubleSide,
                                        transparent: true
                                    }),
                                    new THREE.ShaderMaterial({
                                        uniforms: selectedStickMaterialUniforms[1],
                                        attributes: attributes,
                                        vertexShader: vertexShader,
                                        fragmentShader: fragmentShader,
                                        side: THREE.DoubleSide,
                                        transparent: true
                                    })
                                ];

                                stick = new THREE.Mesh(geometry, normalStickMaterial);
                                stick.frustumCulled = false;
                                stick.matrixAutoUpdate = false;
                                stick.updateMatrix();
                                stick.userData = stickData;
                                stick.normalMaterial = normalStickMaterial;
                                stick.selectedMaterial = selectedStickMaterial[stickData.typeIndex];
                                sticksGroup.add(stick);
                                sticksWidth = (stickWidth + stickPadding) * stickCount;
                            }
                        }

                        camera.position.set(0, 35, 43.8);
                        camera.lookAt(new THREE.Vector3(0, 0, -400 / 3));
                        camera.position.setX(stickWidth * 5);

                        window.addEventListener('resize', onWindowResize, false);
                        document.addEventListener('mousemove', onDocumentMouseMove, false);

                        animate();

                        if (readyCallback) readyCallback();
                    });
                }

                init();
            }});
        }});

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
            var intersects = ray.intersectObjects(sticksGroup.children);
            var selectedStick1;
            if (intersects.length > 0) selectedStick1 = intersects[0].object;
            if (selectedStick1 != selectedStick) {
                if (selectedStick1) selectedStick1.material = selectedStick1.selectedMaterial;
                if (selectedStick) selectedStick.material = selectedStick.normalMaterial;
                selectedStick = selectedStick1;
                updateInfoBox();

                render();
            }
        }

        function onDocumentMouseMove(event) {

            mouseX = ( event.clientX - windowHalfX );
            mouseY = -( event.clientY - windowHalfY );

            if (fillAniFinished) focus(event);
        }

        function sign(val) {
            if (val == 0) return 0;
            else return val > 0 ? 1 : -1;
        }

        function clampAbs(val, abs) {
            abs = Math.abs(abs);
            if (Math.abs(val) > abs) val = sign(val) * abs;
            return val;
        }

        function calFittingWidth(viewWidth, camera, distance) {
            /*
            var fovHorizontal = Math.radToDeg(Math.atan(distance*Math.tan(Math.degToRad(camera.fov)/2)*camera.aspect/distance)*2);
            return viewWidth+distance*Math.tan(Math.degToRad(fovHorizontal)/2)*2;
            */
            return viewWidth+distance*(Math.tan(Math.degToRad(camera.fov)/2)*camera.aspect)*2;
        }

        function animate() {
            requestAnimationFrame(animate);

            if (!fillAniFinished && 100 - fillPercentValue.value > 0.1) {
                fillPercentValue.value += 1;
                if (Math.abs(100 - fillPercentValue.value) < 0.1) {
                    fillPercentValue.value = 100;
                    fillAniFinished = true;
                }
            }

            if (Math.abs(mouseX) - windowHalfX * 0.1 > 0) {
                var sgn=sign(mouseX);
                var d=(Math.abs(mouseX) - windowHalfX * 0.1)/(windowHalfX*0.9);
                scrollSpeedAcc = sgn*d*d*d*10;
            } else {
                scrollSpeedAcc = 0;
            }
            scrollSpeed += scrollSpeedAcc;
            scrollSpeed *= scrollSpeedStopDelta;
            if (Math.abs(scrollSpeed) < scrollSpeedMin) scrollSpeed = 0;
            else if (Math.abs(scrollSpeed) > scrollSpeedMax) scrollSpeed = sign(scrollSpeed) * scrollSpeedMax;

            var cameraX = camera.position.x + scrollSpeed;
            if (cameraX<0) cameraX=0;
            else if (cameraX>sticksWidth) cameraX = sticksWidth;
            camera.position.x += ( cameraX - camera.position.x ) * 0.1;

            render();
        }

        function render() {
            renderer.render(scene, camera);
        }
    })();
}