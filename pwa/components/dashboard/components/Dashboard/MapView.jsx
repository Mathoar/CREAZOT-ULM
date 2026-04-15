'use client';

import { MapContainer, TileLayer, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { useEffect, useState, useRef } from 'react';
import CloudIcon from '@mui/icons-material/Cloud';
import { SelectBalise } from './Map/SelectBalise';
import { LeafletControl } from './Map/LeafletControl';
import { useBalisePositions } from './Map/useBalisePositions';
import { useTrafficPositions } from './Map/useTrafficPositions';
import { isDefined, isDefinedAndNotVoid } from '../../../../app/lib/utils';
import { CircularProgress, Box, Fab } from '@mui/material';
import { LeafletTrackingMarker } from 'react-leaflet-tracking-marker';
import FullscreenIcon from '@mui/icons-material/Fullscreen';
import { clientWithMicrotrakTags } from '../../../../app/lib/client';

const TRAFFIC_ICON_SVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28">
  <path d="M21 16v-2l-8-5V3.5A1.5 1.5 0 0011.5 2 1.5 1.5 0 0010 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"
    fill="#3b82f6" stroke="#1e3a5f" stroke-width="0.8"/>
</svg>`;

const ForceResize = ({ hidden, fullScreen }) => {
    const map = useMap();
    useEffect(() => {
      const handleVisibilityChange = () => {
        if (document.visibilityState === 'visible' && (!hidden || fullScreen))
          setTimeout(() => setTimeout(() => map.invalidateSize(), 300), 300);
      };
      document.addEventListener('visibilitychange', handleVisibilityChange);
      return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
    }, [map, hidden, fullScreen]);
  return null;
};

const AutoCenter = ({ position, zoom = null }) => {
    const map = useMap();
    useEffect(() => {
      if (position && position.lat && position.lng) {
        map.setView([position.lat, position.lng], (isDefined(zoom) ? zoom : map.getZoom()));
      }
    }, [position]);
    return null;
};

const MapEffect = ({ isSmall, hidden, fullScreen }) => {
  const map = useMap();
  useEffect(() => {
    if (!hidden || fullScreen) {
      setTimeout(() => { map.invalidateSize(); }, 300);
    }
  }, [isSmall, hidden, fullScreen, map]);
  return null;
}

const ResizeHandler = () => {
  const map = useMap();
  useEffect(() => {
    const container = map.getContainer();
    if (!container) return;
    const observer = new ResizeObserver(() => { map.invalidateSize(); });
    observer.observe(container);
    return () => observer.disconnect();
  }, [map]);
  return null;
};

const trafficIcon = L.divIcon({
    className: 'traffic-icon',
    html: TRAFFIC_ICON_SVG,
    iconSize: [28, 28],
    iconAnchor: [14, 14],
    popupAnchor: [0, -14],
});

const MapView = ({ isSmall, switchToMetar, hidden, client, setShowFullMap, selectedBalise, setSelectedBalise, fullScreen = false }) => {

    const mapRef = useRef(null);
    const pollingInterval = 20000;
    const dateTimeOptions = { year: '2-digit', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'};
    const hasTracking = clientWithMicrotrakTags(client);

    const [isChange, setIsChange] = useState(false);
    const [aeronefs, setAeronefs] = useState([]);
    const [prevPos, setPrevPos] = useState({});
    const [prevTrafficPos, setPrevTrafficPos] = useState({});
    const [hasInitializedBalise, setHasInitializedBalise] = useState(false);

    const showTracking = hasTracking && (selectedBalise !== 'none' && selectedBalise !== 'traffic');
    const showTraffic = selectedBalise === 'traffic' || selectedBalise === 'all_traffic';

    const { positions, error } = useBalisePositions(
        (hasInitializedBalise && showTracking ? selectedBalise === 'all_traffic' ? 'all' : selectedBalise : null),
        aeronefs, pollingInterval, isChange, setIsChange, hidden
    );

    const { traffic, trafficError } = useTrafficPositions(showTraffic, pollingInterval, hidden);

    useEffect(() => {
      if (!hidden || fullScreen) {
          const stored = localStorage.getItem('selectedBalise');
          if (stored)
              setSelectedBalise(stored);
          else if (!hasTracking)
              setSelectedBalise('traffic');

          setHasInitializedBalise(true);
      }
    }, [hidden, fullScreen]);

    useEffect(() => {
        const newPrev = {};
        positions.forEach(p => newPrev[p.nombalise] = [p.lat, p.lng]);
        setPrevPos(newPrev);
    }, [positions]);

    useEffect(() => {
        const newPrev = {};
        traffic.forEach(t => newPrev[t.icao24] = [t.lat, t.lng]);
        setPrevTrafficPos(newPrev);
    }, [traffic]);

    useEffect(() => {
      if (selectedBalise !== 'none')
        localStorage.setItem('selectedBalise', selectedBalise);
      else
        localStorage.removeItem('selectedBalise');
    }, [selectedBalise]);

    const onBaliseChange = baliseId => {
      setSelectedBalise(baliseId);
      setIsChange(true);
      setTimeout(() => setIsChange(false), 500);
    };

    const convertToKmh = speedStr => {
      const speedMs = parseFloat(speedStr);
      if (isNaN(speedMs)) return 0;
      return (speedMs * 3.6).toFixed(2);
    };

    const convertKtsToKmh = kts => {
      const v = parseFloat(kts);
      if (isNaN(v)) return 0;
      return (v * 1.852).toFixed(0);
    };

    const parseDate = dateString => {
      const [datePart, timePart] = dateString.split(' ');
      const [day, month, year] = datePart.split('/').map(Number);
      const [hour, minute, second] = timePart.split(':').map(Number);
      return new Date(year, month - 1, day, hour, minute, second);
    };

    const getAltitudeDisplay = (alt) => {
        if (alt === 'ground' || alt === 0) return 'Au sol';
        const ft = Math.round(alt);
        return `${ft} ft (FL${Math.round(ft / 100)})`;
    };

    return (
        <div className={`block w-full mt-6 ${ hidden && !fullScreen ? 'hidden' : ''}`}>
            <div className="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark h-full flex flex-col">
              <div className={`flex-grow ${fullScreen ? (isSmall ? 'min-h-[520px]' : 'min-h-[680px]') : 'min-h-[420px]'}`} style={{ height: '100%', minHeight: fullScreen ? (isSmall ? '520px' : '680px') : '420px' }}
              >
                <MapContainer center={ [client.lat, client.lng] } zoom={ client.zoom + (isSmall ? 0 : 1) } whenCreated={map => (mapRef.current = map)} style={{ height: '100%', width: '100%'}}>
                    <ForceResize hidden={ hidden } fullScreen={ fullScreen }/>
                    <MapEffect isSmall={ isSmall } hidden={ hidden } fullScreen={ fullScreen } />
                    <ResizeHandler />
                    { (selectedBalise === 'none' || selectedBalise === 'traffic' || selectedBalise === 'all_traffic') ?
                        <AutoCenter position={{lat: client.lat, lng: client.lng}} zoom={ client.zoom + (isSmall ? 0 : 1) }/> :
                        selectedBalise !== 'all' && positions.length === 1 && <AutoCenter position={ positions[0] } />
                    }
                    <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                    <LeafletControl position="topright">
                        <SelectBalise value={ selectedBalise } onChange={ onBaliseChange } setAeronefs={ setAeronefs } client={ client }/>
                    </LeafletControl>

                    { isChange && (
                      <Box position="absolute" top="50%" left="50%"
                        sx={{ transform: 'translate(-50%, -50%)', zIndex: 1000, backgroundColor: 'rgba(255,255,255,0.6)', borderRadius: '8px', padding: '1rem' }}
                      >
                        <CircularProgress size={40} />
                      </Box>
                    )}

                    { error && showTracking && <div>Erreur : {error}</div> }

                    {/* Marqueurs balises tracking (icône client) */}
                    {!isChange && !error && showTracking && isDefinedAndNotVoid(positions) && positions.map((pos) => {
                        const id = pos.nombalise || `idx_${pos.lat}_${pos.lng}`;
                        return (
                            <LeafletTrackingMarker
                              key={`track_${id}`}
                              position={[pos.lat, pos.lng]}
                              previousPosition={ prevPos[pos.nombalise] || [pos.lat, pos.lng] }
                              rotationAngle={parseFloat(pos.cap) || 0}
                              duration={pollingInterval}
                              icon={
                                L.divIcon({
                                  className: 'custom-icon',
                                  html: `<img src="${ isDefined(client.mapIcon) && client.mapIcon !== '' ? client.mapIcon : '/images/FlightIcon.png' }" style="width: 40px; height: 40px;"/>`,
                                  iconSize: [40, 40],
                                  iconAnchor: [20, 20],
                                  popupAnchor: [0, -20],
                              })
                              }
                            >
                              <Popup>
                                { pos.nombalise || 'Balise' } <span className={`text-[9px] italic ${ pos.mode === 'SLEEPING' ? 'text-zinc-500' : 'text-lime-500'}`}>{ pos.mode }</span><br />
                                [{ pos.lat.toFixed(5) }, { pos.lng.toFixed(5) }] - { parseDate(pos.time).toLocaleString('fr-FR', dateTimeOptions) }<br />
                                Altitude : { pos.altitude }m<br />
                                Cap : { pos.cap }°<br />
                                Vitesse : { convertToKmh(pos.vitesse) }km/h
                              </Popup>
                            </LeafletTrackingMarker>
                        );
                    })}

                    {/* Marqueurs trafic ADS-B (icône avion bleu) */}
                    {showTraffic && traffic.map((ac) => {
                        if (ac.onGround) return null;
                        return (
                            <LeafletTrackingMarker
                              key={`adsb_${ac.icao24}`}
                              position={[ac.lat, ac.lng]}
                              previousPosition={ prevTrafficPos[ac.icao24] || [ac.lat, ac.lng] }
                              rotationAngle={ac.heading || 0}
                              duration={pollingInterval}
                              icon={trafficIcon}
                            >
                              <Popup>
                                <strong>{ ac.callsign || ac.icao24 }</strong>
                                { ac.callsign && ac.icao24 && <span className="text-[9px] text-zinc-400 ml-1">({ac.icao24})</span> }
                                <br />
                                Altitude : { getAltitudeDisplay(ac.altitude) }<br />
                                Cap : { Math.round(ac.heading) }°<br />
                                Vitesse : { convertKtsToKmh(ac.speed) } km/h ({ Math.round(ac.speed) } kt)<br />
                                { ac.verticalRate !== 0 && <>V/S : { ac.verticalRate > 0 ? '+' : '' }{ Math.round(ac.verticalRate) } ft/min<br /></> }
                                { ac.squawk && <>Squawk : { ac.squawk }<br /></> }
                                { ac.country && <span className="text-[9px] text-zinc-400">{ ac.country }</span> }
                              </Popup>
                            </LeafletTrackingMarker>
                        );
                    })}

                    { !fullScreen && !isSmall &&
                      <Fab
                        color={ client.color || 'primary' }
                        size="medium"
                        onClick={() => setShowFullMap(true)}
                        sx={{ position: 'absolute', bottom: 16, right: 16, zIndex: 1500 }}
                        aria-label="Afficher plein écran"
                      >
                        <FullscreenIcon />
                      </Fab>
                    }
                </MapContainer>
              </div>
              { !fullScreen &&
                  <div className="mt-4 text-left md:hidden">
                      <a href="#" onClick={ switchToMetar } className="inline-flex items-center text-sm gap-1 px-3 py-1 rounded border border-gray-800 text-gray-800 hover:text-red-600 hover:border-red-600 hover:bg-red-50 transition-all md:hidden">
                          <><CloudIcon className="mr-2" />{ "METAR & TAF" }</>
                      </a>
                </div>
              }
            </div>
        </div>
    );
};

export default MapView;
