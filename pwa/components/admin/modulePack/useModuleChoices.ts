import { useState, useEffect } from "react";

const ENTRYPOINT = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

export const useModuleChoices = () => {
  const [choices, setChoices] = useState<{ id: string; name: string }[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchModules = async () => {
      try {
        const res = await fetch(`${ENTRYPOINT}/admin/integrations/modules`, {
          credentials: "include",
        });
        if (res.ok) setChoices(await res.json());
      } catch { /* fallback silencieux */ }
      setLoading(false);
    };
    fetchModules();
  }, []);

  return { choices, loading };
};
