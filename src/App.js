import { React, useEffect } from "react";
import { Route, Routes, Navigate } from "react-router-dom";
import Home from "./pages/Home";
import Admin from "./pages/Admin";
import Header from "./components/Layout/Header";
import Navbar from "./components/Layout/Navbar";
import Footer from "./components/Layout/Footer";
import "./style/App.css";
import ReactGA from 'react-ga4';
const TRACKING_ID = "G-PYK1ZR5YCK";
let showHeader = false;
let showFooter = false;
let showNavbar = false;

if (typeof window.BEEF_APP_SHOW_HEADER === "boolean") {
  showHeader = window.BEEF_APP_SHOW_HEADER;
}

if (typeof window.BEEF_APP_SHOW_FOOTER === "boolean") {
  showFooter = window.BEEF_APP_SHOW_FOOTER;
}

if (typeof window.BEEF_APP_SHOW_NAVBAR === "boolean") {
  showNavbar = window.BEEF_APP_SHOW_NAVBAR;
}

function App() {

  useEffect(() => {
    ReactGA.initialize(TRACKING_ID);
  }, []);

  return (
    <>
      {showHeader && <Header />}
      {showNavbar && <Navbar />}
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/admin" element={<Admin />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>

      {showFooter && <Footer />}
    </>
  );
}

export default App;
