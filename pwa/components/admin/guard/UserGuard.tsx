import SyncLoader from 'react-spinners/SyncLoader';
import OnboardingScreen from './OnboardingScreen';
import SuspendedScreen from './SuspendedScreen';
import { useClient } from '../ClientProvider';
import { useSessionContext } from '../SessionContextProvider';

interface UserGuardProps {
  children: React.ReactNode;
}

const UserGuard = ({ children }: UserGuardProps) => {
  const { userStatus, currentUser, isSuperAdmin, loading, refreshUser } = useClient() || {};
  const { session } = useSessionContext();

  if (isSuperAdmin) {
    return <>{children}</>;
  }

  if (loading || userStatus === 'loading') {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <SyncLoader size={8} color="#46B6BF" />
      </div>
    );
  }

  if (userStatus === 'no_clients') {
    return <OnboardingScreen session={session} currentUser={currentUser} onRequestSent={refreshUser} />;
  }

  if (userStatus === 'no_active') {
    return <SuspendedScreen />;
  }

  return <>{children}</>;
};

export default UserGuard;
