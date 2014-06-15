function sticksCreate(readyCallback) {
    var stickMaxHeight = 420, stickWidth = 3, stickPadding = 0.4, fillAniFinished = false;
    var container, sidePadding;
    var camera, scene, projector, renderer;

    var mouseX = 0, mouseY = 0;
    var scrollSpeed = 0, scrollSpeedMax = 1500, scrollSpeedMin = 0.01, scrollSpeedStopDelta = 0.95, scrollSpeedAcc = 0;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    var infoSprite;
    var fillPercentValue;
    var sticksGroup, sticksWidth, focusedStick;

    var updateTeamData;

    function barHeightCal(distance) {
        var ret = 0;
        if (distance / 5<=1) ret += Math.sin(distance / 5 * Math.PI) * 4;
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
                context.drawImage(image,0,height*TYPES[stick.userData.typeIndex].bgOffsetV/2,width,height/2,0,0,width,height/2);
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
            infoSprite.visible=focusedStick!=undefined;
            if (focusedStick) {
                infoSprite.updateInfo(focusedStick);
            }
        }
    }

    function calSidePadding() {
        var vector0 = new THREE.Vector3( 0, 0, 0.5);
        projector.unprojectVector(vector0, camera);
        var vector = new THREE.Vector3( 12, 0, 0.5);
        projector.unprojectVector(vector, camera);

        sidePadding=vector.x-vector0.x;
    }

    (function () {
        $.ajax({ url: 'shader/vertex.glessl', type: "GET", async: true, cache: false, dataType: "text", success: function (vertexShader) {
            $.ajax({ url: 'shader/fragment.glessl', type: "GET", async: true, cache: false, dataType: "text", success: function (fragmentShader) {
                function init() {
                    container = document.getElementById('container');
                    projector = new THREE.Projector();
                    renderer = new THREE.WebGLRenderer({alpha: true, antialias: true/*, logarithmicDepthBuffer: true*/});
                    renderer.setClearColor(0x000000, 0);
                    var width = window.innerWidth;
                    var height = window.innerHeight - 60;

                    renderer.setSize(width, height);
                    container.appendChild(renderer.domElement);

                    scene = new THREE.Scene();
                    camera = new THREE.PerspectiveCamera(55, width / height, 0.5, 3000000);

                    createInfoSprite({ imageUrl:'image/sticks-infobox.png?'+(new Date()).getTime(), opacity:0.86, size:15, fixedScaleFactor:0.015,
                        finishCallback:function(sprite) {
                            infoSprite=sprite;
                            scene.add(infoSprite);
                        },
                        drawCallback: function(context, data) {
                            context.fillStyle = TYPES[data.typeIndex].color;
                            context.textBaseline = 'top';
                            context.font="32px 'Porsche News Gothic','黑体','sans-serif'";
                            context.fillText('Rank:'+data.ranking+'/'+Math.round(data.teamSize), 21, 16);
                            context.font="28px 'Porsche News Gothic','黑体','sans-serif'";
                            context.fillText(data.team, 23, 73);
                            context.fillText(''+Math.round(data.speed) + 'km/h', 180, 73);
                            context.fillText("Player", 23, 117);
                            context.fillText("Lap:" + data.lap, 180, 117);
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
                    var focusedStickMaterialUniforms =[
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

                    updateTeamData=function(type, callback) {
                        dataApi.getTeamData(type, function (teamData) {
                            if (teamData.status != 0) {
                                console.error('Data status invalid: ' + teamData.message, teamData);
                            } else {
                                while (sticksGroup.children.length>0) sticksGroup.remove(sticksGroup.children[0]); //todo

                                fillAniFinished=false;
                                fillPercentValue.value=0;

                                var stickCount = teamData.data.length, perDistance = stickMaxHeight / stickCount;
                                var teamSizes = [teamData.ext.weibo_total, teamData.ext.twitter_total];
                                for (var i = 0; i < stickCount; i++) {
                                    var stickData = teamData.data[i];
                                    stickData.teamSize = teamSizes[stickData.typeIndex];
                                    var h = i == 0 ? stickMaxHeight : Math.round((perDistance * (stickCount - i + (Math.random() - 0.5) * 0.9)) * 100) / 100;
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
                                    var focusedStickMaterial = [
                                        new THREE.ShaderMaterial({
                                            uniforms: focusedStickMaterialUniforms[0],
                                            attributes: attributes,
                                            vertexShader: vertexShader,
                                            fragmentShader: fragmentShader,
                                            side: THREE.DoubleSide,
                                            transparent: true
                                        }),
                                        new THREE.ShaderMaterial({
                                            uniforms: focusedStickMaterialUniforms[1],
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
                                    stick.selectedMaterial = focusedStickMaterial[stickData.typeIndex];
                                    sticksGroup.add(stick);
                                    sticksWidth = (stickWidth + stickPadding) * stickCount;
                                }
                            }

                            if (callback) callback();
                        });
                    };

                    camera.position.set(0, 35, 43.8);
                    camera.lookAt(new THREE.Vector3(0, 0, -400 / 3));
                    camera.position.setX(stickWidth * 5);
                    calSidePadding();

                    window.addEventListener('resize', onWindowResize, false);
                    document.addEventListener('mousemove', onDocumentMouseMove, false);

                    animate();

                    if (readyCallback) readyCallback(updateTeamData);
                }

                init();
            }});
        }});

        function onWindowResize() {
            windowHalfX = window.innerWidth / 2;
            windowHalfY = window.innerHeight / 2;
            var width = window.innerWidth;
            var height = window.innerHeight - 60;
            camera.aspect = width / height;
            camera.updateProjectionMatrix();

            renderer.setSize(width, height);
            calSidePadding();
        }

        function focus() {
            var vector = new THREE.Vector3( mouseX / windowHalfX, mouseY / windowHalfY, 0.5);
            projector.unprojectVector(vector, camera);
            var ray = new THREE.Raycaster(camera.position, vector.sub(camera.position).normalize());
            var intersects = ray.intersectObjects(sticksGroup.children);
            var focusedStick1;
            if (intersects.length > 0) focusedStick1 = intersects[0].object;
            if (focusedStick1 != focusedStick) {
                if (focusedStick1) focusedStick1.material = focusedStick1.selectedMaterial;
                if (focusedStick) focusedStick.material = focusedStick.normalMaterial;
                focusedStick = focusedStick1;
                updateInfoBox();

                render();
            }
        }

        function onDocumentMouseMove(event) {
            if (typeof event.offsetX === "undefined" || typeof event.offsetY === "undefined") {
                var targetOffset = $(event.target).offset();
                event.offsetX = event.pageX - targetOffset.left;
                event.offsetY = event.pageY - targetOffset.top;
            }

            mouseX = ( event.offsetX - windowHalfX );
            mouseY = -( event.offsetY - windowHalfY );

            if (fillAniFinished) focus();
        }

        function sign(val) {
            if (val == 0) return 0;
            else return val > 0 ? 1 : -1;
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
                scrollSpeedAcc = sgn*d*4;
                if (sign(scrollSpeedAcc)!=sign(scrollSpeed)) scrollSpeed=0;
            } else {
                scrollSpeedAcc = 0;
            }
            scrollSpeed += scrollSpeedAcc;
            scrollSpeed *= scrollSpeedStopDelta;
            if (Math.abs(scrollSpeed) < scrollSpeedMin) scrollSpeed = 0;
            else if (Math.abs(scrollSpeed) > scrollSpeedMax) scrollSpeed = sign(scrollSpeed) * scrollSpeedMax;

            var cameraX = camera.position.x + scrollSpeed;

            if (cameraX<sidePadding) cameraX=sidePadding;
            else if (cameraX>sticksWidth-sidePadding) cameraX = sticksWidth-sidePadding;
            camera.position.x += ( cameraX - camera.position.x ) * 0.1;

            render();
        }

        function render() {
            renderer.render(scene, camera);
        }
    })();
}