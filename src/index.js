import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import { BrowserRouter } from 'react-router-dom';

import "./style/index.css";

let basename = "/Estrus-Synchronization-Planner-Staging";
if (typeof window.BEEF_APP_BASENAME === "string") {
  basename = window.BEEF_APP_BASENAME;
}

window.renderBeefApp = function() {
  const rootElement = document.getElementById('root');
  if (!rootElement) return;

  const root = ReactDOM.createRoot(rootElement);
  root.render(
    <React.StrictMode>
      <BrowserRouter basename={basename}>
        <App />
      </BrowserRouter>
    </React.StrictMode>
  );
};

if (document.getElementById('root')) {
  window.renderBeefApp();
}