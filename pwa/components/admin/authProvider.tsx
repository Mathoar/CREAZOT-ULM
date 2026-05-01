import { AuthProvider } from "react-admin";
import { getSession, signIn, signOut } from "next-auth/react";

import { NEXT_PUBLIC_OIDC_SERVER_URL } from "../../config/keycloak";

const forceSignIn = () => {
  sessionStorage.removeItem('client');
  window.location.href = `/api/auth/signin?callbackUrl=${encodeURIComponent(window.location.href)}`;
};

const authProvider: AuthProvider = {
  login: async () => Promise.resolve(),
  logout: async () => {
    const session = await getSession();
    if (!session) {
      return;
    }

    sessionStorage.removeItem('client');

    await signOut({
      // @ts-ignore
      callbackUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}`,
    });
  },
  checkError: async (error) => {
    const session = await getSession();
    const status = error.status;
    // @ts-ignore
    if (!session || session?.error === "RefreshAccessTokenError" || status === 401) {
      forceSignIn();
      return;
    }

    if (status === 403) {
      return Promise.reject({ message: "Unauthorized user!", logoutUser: false });
    }
  },
  checkAuth: async () => {
    const session = await getSession();
    // @ts-ignore
    if (!session || session?.error === "RefreshAccessTokenError") {
      forceSignIn();
      return;
    }

    return Promise.resolve();
  },
  getPermissions: () => Promise.resolve(),
  // @ts-ignore
  getIdentity: async () => {
    const session = await getSession();
    // @ts-ignore
    return session?.user ?? null;
  },
  handleCallback: () => Promise.resolve(),
};

export default authProvider;
