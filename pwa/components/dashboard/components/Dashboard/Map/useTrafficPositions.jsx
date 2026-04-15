import { useEffect, useState, useRef } from "react";
import axios from "axios";
import { useSessionContext } from '../../../../admin/SessionContextProvider';
import { useClient } from '../../../../admin/ClientProvider';

export const useTrafficPositions = (enabled = false, pollingInterval = 20000, hidden = false) => {
    const intervalRef = useRef(null);
    const [traffic, setTraffic] = useState([]);
    const [trafficError, setTrafficError] = useState(null);
    const { session } = useSessionContext();
    const { client } = useClient();

    const getHeaders = () => ({
        Authorization: `Bearer ${session?.accessToken}`,
        'X-Client-Id': client?.id?.toString(),
    });

    useEffect(() => {
        const handleVisibilityChange = () => {
            if (document.visibilityState === "visible")
                startPolling();
            else
                stopPolling();
        };

        document.addEventListener("visibilitychange", handleVisibilityChange);

        if (document.visibilityState === "visible")
            startPolling();

        return () => {
            stopPolling();
            document.removeEventListener("visibilitychange", handleVisibilityChange);
        };
    }, []);

    useEffect(() => {
        stopPolling();
        if (enabled && !hidden && document.visibilityState === "visible")
            startPolling();
        else if (!enabled)
            setTraffic([]);
    }, [enabled, hidden, client?.id, session?.accessToken]);

    const startPolling = () => {
        if (!intervalRef.current && enabled && !hidden) {
            fetchTraffic();
            intervalRef.current = setInterval(fetchTraffic, pollingInterval);
        }
    };

    const stopPolling = () => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
            intervalRef.current = null;
        }
    };

    const fetchTraffic = async () => {
        if (!enabled || hidden || !session?.accessToken || !client?.id) return;

        try {
            const headers = getHeaders();
            const response = await axios.get('/admin/integrations/run/live_traffic', { headers });
            const data = response.data;

            const aircraft = (data?.ac || data?.states || [])
                .filter(ac => ac.lat != null && ac.lon != null)
                .map(ac => ({
                    icao24: ac.hex || ac[0] || '',
                    callsign: (ac.flight || ac[1] || '').trim(),
                    country: ac.country || ac[2] || '',
                    lat: ac.lat ?? ac[6],
                    lng: ac.lon ?? ac[5],
                    altitude: Math.round(ac.alt_baro || ac.alt_geom || ac[7] || 0),
                    onGround: ac.ground === true || ac.alt_baro === 'ground' || ac[8] === true,
                    speed: ac.gs || (ac[9] ? ac[9] * 1.944 : 0),
                    heading: ac.track ?? ac[10] ?? 0,
                    verticalRate: ac.baro_rate || ac[11] || 0,
                    squawk: ac.squawk || ac[14] || '',
                    category: ac.category || '',
                    isTraffic: true,
                }));

            setTraffic(aircraft);
            setTrafficError(null);
        } catch (err) {
            if (err?.response?.status !== 429) {
                setTrafficError(err.message || 'Erreur trafic ADS-B');
            }
        }
    };

    return { traffic, trafficError };
};
