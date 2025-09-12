import React from "react";
import DriverDetails from "./pages/Route/DriverDetailsSimple";
import GuideDetails from "./pages/Route/GuideDetails";
import {
  BrowserRouter as Router,
  Routes,
  Route,
  useLocation,
} from "react-router-dom";
import "./App.css";
import "bootstrap/dist/css/bootstrap.min.css";
import "./Components/styles/global.css";

// Common components
import Header from "./Components/Header/Header";
import HeaderDashboard from "./Components/Header/HeaderDashboard";
import Footer from "./Components/Footer/Footer";
import ScrollToTop from "./Components/ScrollToTop";

// Pages
import HeroSection from "./pages/Home/HeroSection";
import FeaturesSection from "./pages/Home/FeaturesSection";
import PopularDestinations from "./pages/Home/PopularDestinations";
// import BecomeProviderSection from "./Components/BecomeProviderSection";
import LocalEventsFoods from "./pages/Home/LocalEventsFoods";

import Cultural from "./pages/Culture/Cultural";
import RoutePlanner from "./pages/Route/RoutePlanner";
import BudgetSelection from "./pages/Budget/BudgetSelection";
import GuideRegistrationForm from "./pages/Guide_Registration/GuideRegistrationForm";
import DriverRegistrationForm from "./pages/Driver_Registration/DriverRegistrationForm";
import TravelerRegistrationForm from "./pages/Traveller_Registration/TravelerRegistrationForm";
import LoginPage from "./pages/Login/LoginPage";
import ForgotPasswordPage from "./pages/Login/ForgotPasswordPage";
import VerifyOTPPage from "./pages/Login/VerifyOTPPage";
import ResetPasswordPage from "./pages/Login/ResetPasswordPage";
import BookDriver from "./pages/Route/BookDriver";
import AboutUs from "./pages/AboutUs/AboutUs";
import TermsCondition from "./pages/TermsCondition/TermsConditions";
import PrivacyPolicy from "./pages/PrivacyPolicy/PrivacyPolicy";
import ContactUs from "./pages/ContactUs/ContactUs";

// Dashboards
import DriverDashboard from "./Components/dashboard/driver/DriverDashboard/DriverDashboard";
import GuideDashboard from "./Components/dashboard/guide/GuideDashboard/GuideDashboard";
import AdminDashboard from "./Components/dashboard/admin/AdminDashboard";
import TravelerDashboard from "./Components/dashboard/traveler/TravelerDashboard";
import HeadSection from "./pages/Home/headsection";

const HomePage = () => (
  <>
    <HeadSection />
    <HeroSection />
    <FeaturesSection />
    <PopularDestinations />
    {/* <BecomeProviderSection />} */}
    <LocalEventsFoods />
  </>
);

// Helper to detect if path is dashboard
const isDashboardRoute = (pathname) =>
  pathname.startsWith("/driver-dashboard") ||
  pathname.startsWith("/guide-dashboard") ||
  pathname.startsWith("/admin-dashboard") ||
  pathname.startsWith("/traveller-dashboard");

// Component that renders layout based on current route
const AppContent = () => {
  const location = useLocation();
  const isDashboard = isDashboardRoute(location.pathname);

  return (
    <div className="App">
      {/* Conditional header */}
      {isDashboard ? <HeaderDashboard /> : <Header />}

      <main style={{ marginTop: isDashboard ? "0" : "0px" }}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          {/* Core Pages */}
          <Route path="/homepage" element={<HomePage />} />
          <Route
            path="/guide-registration"
            element={<GuideRegistrationForm />}
          />
          <Route
            path="/traveler-register"
            element={<TravelerRegistrationForm />}
          />
          <Route
            path="/driver-registration"
            element={<DriverRegistrationForm />}
          />
          <Route path="/user-login" element={<LoginPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/verify-otp" element={<VerifyOTPPage />} />
          <Route path="/reset-password" element={<ResetPasswordPage />} />
          <Route path="/budget" element={<BudgetSelection />} />
          <Route path="/culture" element={<Cultural />} />
          <Route path="/route" element={<RoutePlanner />} />
          <Route path="/bookdriver" element={<BookDriver />} />
          <Route path="/driver/:id" element={<DriverDetails />} />
          <Route path="/guide/:id" element={<GuideDetails />} />

          {/* Dashboards */}
          <Route path="/driver-dashboard" element={<DriverDashboard />} />
          <Route path="/guide-dashboard" element={<GuideDashboard />} />
          <Route path="/admin-dashboard" element={<AdminDashboard />} />
          <Route path="/traveller-dashboard" element={<TravelerDashboard />} />

          {/* Info Pages */}
          <Route path="/aboutus" element={<AboutUs />} />
          <Route path="/termsconditions" element={<TermsCondition />} />
          <Route path="/privacypolicy" element={<PrivacyPolicy />} />
          <Route path="/contactus" element={<ContactUs />} />
        </Routes>
      </main>

      {/* Footer only if not a dashboard */}
      {!isDashboard && <Footer />}
    </div>
  );
};

function App() {
  return (
    <Router>
      <ScrollToTop />
      <AppContent />
    </Router>
  );
}

export default App;
