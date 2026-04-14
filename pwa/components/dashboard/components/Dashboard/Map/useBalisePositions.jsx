import { useEffect, useState, useRef } from "react";
import { isDefined } from '../../../../../app/lib/utils';
import axios from "axios";
import { useSessionContext } from '../../../../admin/SessionContextProvider';
import { useClient } from '../../../../admin/ClientProvider';

export const useBalisePositions = (baliseId, aeronefs = [], pollingInterval = 8000, isChange = false, setIsChange= null, hidden = false) => {
    const intervalRef = useRef(null);
    const [positions, setPositions] = useState([]);
    const [error, setError] = useState(null);
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
        if (document.visibilityState === "visible")
            startPolling(); 
    }, [baliseId, JSON.stringify(aeronefs), hidden, client?.id]);

    const startPolling = () => {
        if (!intervalRef.current && !hidden) {
            fetchPositions();
            intervalRef.current = setInterval(fetchPositions, pollingInterval);
        }
    };

    const stopPolling = () => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
            intervalRef.current = null;
        }
    };

    const fetchPositions = async () => {
        if (isDefined(baliseId) && !hidden && session?.accessToken) {
            if (baliseId === 'none') {
                setPositions([]);
            } else if ((baliseId === "all" ? aeronefs.length > 0 : true)) {
                setError(null);
                try {
                    const headers = getHeaders();
                    if (baliseId === "all") {
                        const positionsPromises = aeronefs.map(balise =>
                            axios.get(`/admin/integrations/run/tracking/${balise.codeBalise}`, { headers })
                                .then(res => getTransformedData(res, balise))
                                .catch(() => null)
                        );
                        
                        const allPositions = (await Promise.all(positionsPromises)).filter(Boolean);;
                        setPositions(allPositions);
                    } else {
                        const aeronef = aeronefs.find(a => a.codeBalise === baliseId) || {immatriculation: "", codeBalise: ""};
                        const response = await axios.get(`/admin/integrations/run/tracking/${baliseId}`, { headers });
                        const positions = getTransformedData(response, aeronef);
                        setPositions([positions].filter(Boolean));
                    }
                } catch (err) {
                    setError(err.message || 'Erreur lors de la récupération des balises');
                } 
            }
            setIsChange(false);
        }
        return ;
    };

    const getTransformedData = ({ data }, { immatriculation, codeBalise }) => {
        if (!isDefined(data) || !isDefined(data.lat) || !isDefined(data.lng)) 
            return null;

        return {...data, nombalise: immatriculation, deveui: codeBalise };
    }

    return { positions, error };
};
