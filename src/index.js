import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import { BrowserRouter } from 'react-router-dom';

import "./style/index.css";

const root = ReactDOM.createRoot(document.getElementById('root'));
const basename = window.BEEF_APP_BASENAME || "/Estrus-Synchronization-Planner-Staging";

root.render(
  <React.StrictMode>
    <BrowserRouter basename={basename}>
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
