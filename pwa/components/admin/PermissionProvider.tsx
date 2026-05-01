"use client";

import React, { createContext, useContext, useEffect, useState, useCallback, useRef } from 'react';
import { useSession } from 'next-auth/react';
import { useClient } from './ClientProvider';
import type { Session } from '../../app/auth';

interface Permissions {
  [resource: string]: { read: boolean; write: boolean };
}

interface PermissionContextType {
  permissions: Permissions;
  role: string | null;
  roleLabel: string | null;
  loading: boolean;
  canRead: (resource: string) => boolean;
  canWrite: (resource: string) => boolean;
  refreshPermissions: () => void;
}

const PermissionContext = createContext<PermissionContextType>({
  permissions: {},
  role: null,
  roleLabel: null,
  loading: true,
  canRead: () => false,
  canWrite: () => false,
  refreshPermissions: () => {},
});

export const PermissionProvider = ({ children }: { children: React.ReactNode }) => {
  const { data: session } = useSession() as { data: Session | null };
  const { client, isSuperAdmin } = useClient() || {};
  const [permissions, setPermissions] = useState<Permissions>({});
  const [role, setRole] = useState<string | null>(null);
  const [roleLabel, setRoleLabel] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const accessTokenRef = useRef(session?.accessToken);
  accessTokenRef.current = session?.accessToken;

  const fetchPermissions = useCallback(async () => {
    if (!accessTokenRef.current || !client?.id) {
      setLoading(false);
      return;
    }

    try {
      const res = await fetch('/api/me/permissions', {
        headers: {
          'Authorization': `Bearer ${accessTokenRef.current}`,
          'X-Client-Id': String(client.id),
          'Accept': 'application/json',
        },
      });

      if (res.ok) {
        const data = await res.json();
        setPermissions(data.permissions || {});
        setRole(data.role || null);
        setRoleLabel(data.roleLabel || null);
      }
    } catch (e) {
      console.error('Erreur chargement permissions', e);
    } finally {
      setLoading(false);
    }
  }, [client?.id]);

  useEffect(() => {
    setLoading(true);
    fetchPermissions();
  }, [fetchPermissions]);

  const canRead = useCallback((resource: string): boolean => {
    if (isSuperAdmin) return true;
    return permissions[resource]?.read ?? false;
  }, [permissions, isSuperAdmin]);

  const canWrite = useCallback((resource: string): boolean => {
    if (isSuperAdmin) return true;
    return permissions[resource]?.write ?? false;
  }, [permissions, isSuperAdmin]);

  return (
    <PermissionContext.Provider value={{
      permissions,
      role,
      roleLabel,
      loading,
      canRead,
      canWrite,
      refreshPermissions: fetchPermissions,
    }}>
      {children}
    </PermissionContext.Provider>
  );
};

export const usePermissions = () => useContext(PermissionContext);
