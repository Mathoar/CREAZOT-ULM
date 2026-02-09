"use client";

import { isDefined } from "../../app/lib/utils";
import { Layout } from "../common/Layout";
import { Toaster } from "react-hot-toast";

export default function FormLayout({client, children}: { client: any, children: React.ReactNode }) {

    const contact = client?.website ?? client?.email;
    const url = client?.url ?? 'https://localhost';
    const authUrl = url !== 'https://localhost' ? `${url}/oidc/` : 'http://localhost:8080';

    return (  
        <>
            <Toaster position="top-right" />
            <h1 className="main-title text-center text-3xl font-bold mt-4 top-0">{ client?.thanksTitle ?? "FORMULAIRE D'ENREGISTREMENT"}</h1>
            <Layout>
                <div className="flex justify-center md:overflow-y-scroll z-50">
                    <div className="flex-grow max-w-screen-sm p-6 md:overflow-y-auto md:p-12">
                        {children}
                    </div>
                </div>
            </Layout>
            <footer className=" rounded-lg shadow m-4 bottom-0 sticky md:absolute">
                <div className="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between"> 
                <span className="text-sm sm:text-center">© { new Date().getFullYear() } <a href="https://creazot.com/" className="hover:underline">CRÉAZOT™</a>. All Rights Reserved.  {/* text-gray-500 dark:text-gray-400 */}
                </span>
                 <ul className="flex flex-wrap items-center mt-3 text-sm font-medium sm:mt-0">
                    { isDefined(contact) &&
                        <li>
                            <a href={ contact ?? "#" } className="hover:underline me-4 md:me-6 discreet-link">Contactez-nous</a>
                        </li>
                    }
                    <li>
                        <a href={`${ url }/admin`} className="hover:underline me-4 md:me-6 discreet-link">Accès licenciés</a>
                    </li>
                    <li>
                        <a href={`${ authUrl }`} className="hover:underline discreet-link">Administration</a>
                    </li>
                </ul>
                </div>
            </footer>
        </>
    );
  }