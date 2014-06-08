define(function( require , exports , model ){
    require('jquery');
    require('../race/three');
    require('../race/svgTool');
    require('../race/slope');

	var container;
	var camera, scene, renderer, clock;
	var cameraMap, sceneMap, rendererMap;
	var cameraLookAtPosition=new THREE.Vector3(0,0,0);
	var cameraUpAdd=new THREE.Vector3(0,0,0);
	var mouseX = 0, mouseY = 0, radius;
	var windowHalfX = window.innerWidth / 2;
	var windowHalfY = window.innerHeight / 2;

	var mapPadding=100;

	var mapCarSize=30;
	var pathSegments=500;
	var trackPathSplineCurve3, trackOffset, trackLength;
	var meshMapRed, meshMapBlue;
	var meshTrack, meshCarRed, meshCarBlue;
	var redSpeed=81, blueSpeed=80, speed0=100;
	var redDistance=0, blueDistance=0;
	var sideShiftPercents;

	function modPercent(percent) {
		while (percent>1) percent--;
		while (percent<0) percent++;
		return percent;
	}

	function getPositionByPercent(percent) {
		percent=modPercent(percent);
		return trackPathSplineCurve3.getPointAt(percent).add(trackOffset);
	}

	function getTangentByPercent(percent) {
		percent=modPercent(percent);
		return trackPathSplineCurve3.getTangentAt(percent);
	}

	function getPositionByDistance(distance) {
		return getPositionByPercent(distance/trackLength);
	}

	function getTangentByDistance(distance) {
		return getTangentByPercent(distance/trackLength);
	}

	function setMapPosition(ColorMesh, distance) {
		ColorMesh.position.copy(getPositionByDistance(distance));
	}

	var carStepFactor=0.1; //菜单平滑系数
	function calSideShift(percent){
		var shift=1;
		for (var i=0;i<sideShiftPercents.length;i++) {
			if (percent>=sideShiftPercents[i]) {
				shift= i%2==0?-1:1;
			}
		}
		return shift;
	}
	function seTrackPosition(ColorMesh, distance, sideOffset, speed, smooth) {
		if (smooth===undefined) smooth=true;
		for (var i=0;i<ColorMesh.geometry.vertices.length;i+=2) {
			var d=distance-i*speed/speed0;
			var p=getPositionByDistance(d);
			var t=getTangentByDistance(d);
			var side= t.cross(new THREE.Vector3(0,1,0));
			var offset= side.clone().multiplyScalar(sideOffset*calSideShift(modPercent(d/trackLength)*100));
			var v=ColorMesh.geometry.vertices[i+1];
			var p1=p.clone().add(offset).add(side.clone().multiplyScalar(2));
			if (smooth) v.add(p1.sub(v).multiplyScalar(carStepFactor));
			else v.copy(p1);
			var v=ColorMesh.geometry.vertices[i];
			var p1=p.clone().add(offset).add(side.negate().multiplyScalar(2));
			if (smooth) v.add(p1.sub(v).multiplyScalar(carStepFactor));
			else v.copy(p1);
		}
	}

	function setRedDistance(distance, smooth){
		setMapPosition(meshMapRed, distance);
		seTrackPosition(meshCarRed, distance, 3, redSpeed, smooth);
	}

	function setBlueDistance(distance, smooth){
		setMapPosition(meshMapBlue, distance);
		seTrackPosition(meshCarBlue, distance, -3, blueSpeed, smooth);
	}

	$(function($){
		$.ajax({ url:'/js/race/data/track.json', type:"GET", async:true, cache:false, dataType: "json", success:function(track){
			var cameraFollowConf=track.cameraFollow;
			sideShiftPercents=track.sideShiftPercents;
			var svgFile=track.svgFile;
			if (svgFile[0]!=='/') svgFile='data/'+svgFile;
			$.ajax({ url:svgFile, type:"GET", async:true, cache:false, dataType: "xml", success:function(svgDoc){
				var pathstr=$('g path',svgDoc.documentElement).attr('d');
				init();
				animate();

				function init() {

					container = document.getElementById('container' );
					renderer = new THREE.CanvasRenderer({alpha: true});
					renderer.setClearColor(0x000000, 0);
					var width = window.innerWidth;
					var height = window.innerHeight - $('.header').height();
					renderer.setSize( width , height );
					container.appendChild( renderer.domElement );

					scene = new THREE.Scene();
					camera = new THREE.PerspectiveCamera( 55, width / height, 0.5, 3000000 );

					clock = new THREE.Clock();

					function makeShape(width, height, offsetWidth, offsetHeight) {
						if (offsetWidth===undefined) offsetWidth=0;
						if (offsetHeight===undefined) offsetHeight=0;
						var rectWidth=width, rectHeight=height;
						var rectShape = new THREE.Shape();

						rectShape.moveTo( -rectWidth/2+offsetWidth, -rectHeight/2+offsetHeight );
						rectShape.lineTo( -rectWidth/2+offsetWidth, rectHeight/2+offsetHeight );
						rectShape.lineTo( rectWidth/2+offsetWidth, rectHeight/2+offsetHeight );
						rectShape.lineTo( rectWidth/2+offsetWidth, -rectHeight/2+offsetHeight );
						rectShape.lineTo( -rectWidth/2+offsetWidth, -rectHeight/2+offsetHeight );

						return rectShape;
					}

					var path = transformSVGPath(pathstr);
					var length=path.getLength();
					var slopesVertices=[];
					for (var i in track.slopes) {
						var slope=track.slopes[i];
						slopesVertices.push(new THREE.Vector3(slope.percent/100*length,slope.offset*track.slopeScale,0));
					}
					slopePathSplineCurve3 =  new THREE.ClosedSplineCurve3( slopesVertices);
					var vectices2D=path.getSpacedPoints(pathSegments,true);
					var vertices=[];
					for (var i=0;i<vectices2D.length;i++) {
						vertices.push(new THREE.Vector3(vectices2D[i].x,slopePathSplineCurve3.getPointAt(i/vectices2D.length).y/*?*/,vectices2D[i].y));
					}

					var extrudeSettings = { steps: pathSegments, bevelEnabled:false }; // bevelSegments: 2, steps: 2 , bevelSegments: 5, bevelSize: 8, bevelThickness:5,
					trackPathSplineCurve3 =  new THREE.ClosedSplineCurve3( vertices);
					trackLength=trackPathSplineCurve3.getLength();
					extrudeSettings.extrudePath =trackPathSplineCurve3;

					var trackGeometry=makeShape(0.01, 10+2).extrude( extrudeSettings );
					sceneMap = new THREE.Scene();
					meshMap=new THREE.Mesh(
						trackGeometry,
						new THREE.MeshBasicMaterial( {color:0xffffff, overdraw: 0.3 } )
					);;
					meshMap.geometry.computeBoundingSphere();
					trackOffset=meshMap.geometry.boundingSphere.center.clone().negate();
					meshMap.geometry.computeBoundingBox();
					var bb=meshMap.geometry.boundingBox;
					var x0=bb.max.x-bb.min.x+mapPadding*2;
					var z0=bb.max.z-bb.min.z+mapPadding*2;
					var y0=bb.max.y-bb.min.y+mapPadding*2;
					var w=200, h=z0/x0*w;
					meshMap.position.add(trackOffset);
					sceneMap.add( meshMap );
					map = document.getElementById('map' );
					rendererMap = new THREE.CanvasRenderer({alpha: true});
					rendererMap.setClearColor(0x000000, 0);
					rendererMap.setSize( w,h );
					map.appendChild( rendererMap.domElement );
					cameraMap = new THREE.OrthographicCamera( x0 / - 2, x0 / 2, z0 / 2, z0 / - 2, - 500, 2000 );
					cameraMap.position.set( 0, 1000, 0 );
					cameraMap.up.set(0,0,-1);
					cameraMap.lookAt( new THREE.Vector3( 0, 0, 0 ) );

					meshMapRed=new THREE.Mesh(new THREE.SphereGeometry(mapCarSize), new THREE.MeshBasicMaterial({color:0xff0000}) );
					meshMapRed.position.set(0,100,0);
					sceneMap.add(meshMapRed);

					meshMapBlue=new THREE.Mesh(new THREE.SphereGeometry(mapCarSize), new THREE.MeshBasicMaterial({color:0x0000ff}) );
					meshMapBlue.position.set(0,100,0);
					sceneMap.add(meshMapBlue);

					meshTrack = new THREE.Mesh(
						trackGeometry,
						new THREE.MeshBasicMaterial( {color:0xffffff, overdraw: 0.6 } )
					);
					meshTrack.geometry.computeBoundingSphere();
					meshTrack.position.add(trackOffset);
					scene.add( meshTrack );

					radius=meshTrack.geometry.boundingSphere.radius;

					var geometry=new THREE.PlaneGeometry(5,100,1,50);
					meshCarRed = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial( {color:0xff0000, overdraw:0.2, opacity:0.8, side:THREE.DoubleSide} ));
					meshCarRed.frustumCulled=false;
					meshCarRed.position.set(0,5,0);
					setRedDistance(0,false)
					scene.add( meshCarRed );

					var geometry=new THREE.PlaneGeometry(10,100,1,50);
					meshCarBlue = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial( {color:0x0000ff, overdraw:0.2, opacity:0.8, side:THREE.DoubleSide} ));
					meshCarBlue.frustumCulled=false;
					meshCarBlue.position.set(0,8,0);
					setBlueDistance(0,false);
					scene.add( meshCarBlue );

					var PI2 = Math.PI * 2;

					var programStroke = function ( context ) {
						var sizeHalf=track.particle.size/2;
						context.lineWidth = 0.01;
						context.rotate(-clock.getElapsedTime()*Math.PI*1.5);
						context.beginPath();
						context.moveTo(sizeHalf/2, sizeHalf/2 );
						context.lineTo(-sizeHalf/2, sizeHalf/2 );
						context.lineTo(-sizeHalf/2, -sizeHalf/2 );
						context.lineTo(sizeHalf/2, -sizeHalf/2 );
						context.closePath();
						context.stroke();

					}
					for (var i=0;i<track.particle.count;i++) {
						var particle = new THREE.Sprite(new THREE.SpriteCanvasMaterial({ color: 0xcccccc, opacity:0.6, program: programStroke }));
						particle.position.copy(getPositionByPercent(i/100)).add(new THREE.Vector3((Math.random()-0.5) * 150, (Math.random()-0.5) *150, (Math.random()-0.5) * 150));
						particle.scale.multiplyScalar(5+ Math.random() * 10);
						scene.add(particle);
					}

					camera.position.set( cameraFollowConf.initPosition.x, cameraFollowConf.initPosition.y, cameraFollowConf.initPosition.z );
					camera.lookAt( getPositionByPercent(0) );

					window.addEventListener( 'resize', onWindowResize, false );
					document.addEventListener( 'mousemove', onDocumentMouseMove, false );
				}

				function onWindowResize() {
					var width = window.innerWidth;
					var height = window.innerHeight - $('.header').height();

					
					windowHalfX = width / 2;
					windowHalfY = height / 2;
					camera.aspect = width / height;
					camera.updateProjectionMatrix();

					renderer.setSize( width, height );

				}

				function onDocumentMouseMove(event) {

					mouseX = ( event.clientX - windowHalfX );
					mouseY = -( event.clientY - windowHalfY );

				}

				function cameraFollowPosition(targetDistance) {
					var modPer=modPercent(targetDistance/trackLength)*100;
					var cameraLocation;
					for (var i=0;i<cameraFollowConf.locations.length;i++) {
						if (modPer>=cameraFollowConf.locations[i].trackPercent) {
							cameraLocation=cameraFollowConf.locations[i];
						} else break;
					}
					var cameraPosition=getPositionByDistance(targetDistance+cameraLocation.distance);
					if (cameraLocation.height) cameraPosition.y+=cameraLocation.height;
					return cameraPosition;
				}

				function cameraFollow() {
					var distance = Math.max(redDistance, blueDistance);
					var cameraLookTarget=getPositionByDistance(distance).add(cameraUpAdd);

					cameraLookAtPosition.add(cameraLookTarget.sub(cameraLookAtPosition).multiplyScalar(cameraFollowConf.lookAtFactor));
					var cameraPos=cameraFollowPosition(distance);
					camera.position.add(cameraPos.sub(camera.position).multiplyScalar(cameraFollowConf.followFactor));
					camera.lookAt(cameraLookAtPosition);
				}

				function animate() {
					requestAnimationFrame( animate );
					var delta=clock.getDelta();
					var distanceRed=(delta*Math.random())*redSpeed;
					var distanceBlue=(delta*Math.random())*blueSpeed;
					redDistance+=distanceRed;
					blueDistance+=distanceBlue;
					setRedDistance(redDistance);
					setBlueDistance(blueDistance);
					cameraFollow();

					render();
				}

				function render() {
					renderer.render( scene, camera );
					rendererMap.render( sceneMap, cameraMap );
				}
			}});
		}});
	});
});