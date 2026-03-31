import React from "react";
import { Button } from "@mui/material";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import dayjs from "dayjs";
import "../../style/calendarView.css";
import ReactGA from 'react-ga4';
import { getProtocolsData } from '../../utils/dataLoader';

const ProtocolData = getProtocolsData();

const CalendarView = (props) => {
  const {
    ListOfInstrucitons,
    DateToStartBreeding,
    SynchronizationProtocol,
    GNRH,
    PG,
    SemenType,
    BullTurnIn,
    SystemType,
  } = props;

  // text changes
  let listOfInstrucitons = JSON.parse(JSON.stringify(ListOfInstrucitons));
  let ai_standing_heat_txt = "";
  let ai_with_sexed_semen = "";
  let ai_with_sexed_semen_showing = "";
  let ai_with_sexed_semen_plus_conventional = "";
  let nonestrous_females = "";
  let cidr_device = "";
  let estrus_detection_aid = "";
  let ai_females_showing_estrus = "";

  let mga_time_change = DateToStartBreeding.clone().startOf("day");
  let mga_time_change_2 = DateToStartBreeding.clone().startOf("day");
  let mga_time_change_3 = DateToStartBreeding.clone().startOf("day");
  let dateToStartBreeding = DateToStartBreeding.clone();

  mga_time_change = mga_time_change.subtract(19, "day").startOf("day");
  mga_time_change_2 = mga_time_change_2.subtract(11, "day").startOf("day");
  mga_time_change_3 = mga_time_change_3.subtract(22, "day").startOf("day");

  // Get metadata for dynamic dosage lookup (before SemenType logic so we can use selectedGNRH)
  const paramsMeta = ProtocolData.ParametersMeta || {};
  const gnrhMeta = paramsMeta['GnRH'] || {};
  const pgMeta = paramsMeta['PG'] || {};

  // Build GnRH display string dynamically
  let selectedGNRH;
  if (gnrhMeta[GNRH]?.dosage) {
    selectedGNRH = `${gnrhMeta[GNRH].dosage} ${GNRH} (GnRH)`;
  } else if (GNRH === "GnRH" || !GNRH) {
    selectedGNRH = "(GnRH)";
  } else {
    // Fallback for new products without dosage - just show name
    selectedGNRH = `${GNRH} (GnRH)`;
  }

  // Build PG display string dynamically
  let selectedPG;
  if (pgMeta[PG]?.dosage) {
    selectedPG = `${pgMeta[PG].dosage} ${PG} (PG)`;
  } else if (PG === "PG" || !PG) {
    selectedPG = "(PG)";
  } else {
    // Fallback for new products without dosage - just show name
    selectedPG = `${PG} (PG)`;
  }

  //store if breed females AI 16-22....
  // G14 -> Semen Type
  if (SemenType === "Conventional & Sexed") {
    ai_standing_heat_txt = "Breed females AI 16-22 hours after standing heat.";
    ai_with_sexed_semen = "AI with sexed semen estrous females";
    ai_with_sexed_semen_showing =
      "AI with sexed semen those showing females estrus";
    ai_with_sexed_semen_plus_conventional =
      "AI with sexed semen estrous females.  All others with conventional semen.";
    nonestrous_females = `Inject ${selectedGNRH} to nonestrous females.`;
    cidr_device =
      "Remove the CIDR device and apply estrus detection aid for each female.";
    estrus_detection_aid = "Apply estrus detection aid.";
    ai_females_showing_estrus =
      "AI females showing estrus with sexed semen.  All others with conventional semen.";
  } else {
    ai_standing_heat_txt = "Breed females AI 10-14 hours after standing heat.";
    ai_with_sexed_semen = "AI females in estrus";
    ai_with_sexed_semen_showing = "AI females in estrus";
    ai_with_sexed_semen_plus_conventional = "AI females in estrus";
    nonestrous_females = `Inject ${selectedGNRH} to all females.`;
    cidr_device = "Remove the CIDR device from each female.";
  }

  if (SemenType === "Conventional" && SystemType === "Split Time AI") {
    estrus_detection_aid = "Apply estrus detection aid.";
  }

  // Update the instructions with the date and time
  listOfInstrucitons.forEach((instruction, key) => {
    const pgInstruction = listOfInstrucitons.find((item) => item.isPg);
    let pgDate =  dateToStartBreeding.clone();

    if (pgInstruction) {
      pgDate = dateToStartBreeding
        .clone()
        .add(pgInstruction.dateAdjustment, pgInstruction.dateAdjustmentUnit);
    }
    const breedingDate = dateToStartBreeding.clone();
    let dateReference = breedingDate;

    if (instruction.fromPg) {
      dateReference = pgDate;
    }

    let dateAdjustment = instruction.dateAdjustment;

    if (instruction.dateAdjustment === "BullTurnIn") {
      dateAdjustment = parseInt(BullTurnIn, 10) * 24; // convert to hours
    }

    const mainDate = dateReference.add(
      dateAdjustment,
      instruction.dateAdjustmentUnit
    );

    instruction["dateReference"] = dateReference.format("MM/DD/YYYY HH:mm");
    instruction["date"] = mainDate.format("MM/DD/YYYY");
    instruction["dateTime"] = mainDate.format("MM/DD/YYYY HH:mm");
    instruction["dateDay"] = mainDate.format("dddd");

    instruction.lines.forEach((line) => {
      // update placeholders
      if (line.label === "<<ai_after_standing_heat>>") {
        line.label = ai_standing_heat_txt;
      }
      if (line.label === "<<ai_sexed_semen>>") {
        line.label = ai_with_sexed_semen;
      }

      if (line.label === "<<ai_with_sexed_semen_showing>>") {
        line.label = ai_with_sexed_semen_showing;
      }
      if (line.label === "<<ai_with_sexed_semen_plus_conventional>>") {
        line.label = ai_with_sexed_semen_plus_conventional;
      }
      if (line.label === "<<nonestrous>>") {
        line.label = nonestrous_females;
      }
      if (line.label === "<<cidr_device>>") {
        line.label = cidr_device;
      }
      if (line.label === "<<estrus_detection_aid>>") {
        line.label = estrus_detection_aid;
      }
      if (line.label === "<<ai_females_showing_estrus>>") {
        line.label = ai_females_showing_estrus;
      }
      if (line.label === "<<current_time>>") {
        line.label = dateToStartBreeding.toLocaleString("en-US", {
          hour: "numeric",
          minute: "numeric",
          hour12: true,
        });
      }
      if (line.label === "<<mga_time_change_3>>") {
        line.label =
          "Continue feeding until " +
          mga_time_change_3.format("MM/DD/YYYY") +
          ".";
      }
      if (line.label === "<<mga_time_change_2>>") {
        line.label =
          "Continue feeding until " +
          mga_time_change_2.format("MM/DD/YYYY") +
          ".";
      }
      if (line.label === "<<mga_time_change>>") {
        line.label =
          "Continue feeding until " +
          mga_time_change.format("MM/DD/YYYY") +
          ".";
      }

      // Update the instructions with the selected GNRH and PG
      // Handle new placeholder format
      if (line.label?.includes("<<gnrh_type>>")) {
        line["label"] = line["label"].replace("<<gnrh_type>>", selectedGNRH);
        // Remove duplicate suffix if instruction already had it
        line["label"] = line["label"].replace("(GnRH) (GnRH)", "(GnRH)");
      }
      if (line.label?.includes("<<pg_type>>")) {
        line["label"] = line["label"].replace("<<pg_type>>", selectedPG);
        // Remove duplicate suffix if instruction already had it
        line["label"] = line["label"].replace("(PG) (PG)", "(PG)");
      }
      // Handle legacy hardcoded format
      if (line.label?.includes("2cc Cystorelin")) {
        line["label"] = line["label"].replace(
          "2cc Cystorelin (GnRH)",
          selectedGNRH
        );
      }
      if (line.label?.includes("5cc Lutalyse")) {
        line["label"] = line["label"].replace("5cc Lutalyse (PG)", selectedPG);
      }

      // Add line with hour if it's that type of line
      if (line.hourAdjustment) {
        line["label"] = mainDate
          .add(line.hourAdjustment, "hour")
          .format("h:mm A");
      }
    });
  });

  let calEventArr = [];
  let minDate = dayjs(listOfInstrucitons[0]["dateTime"], "MM/DD/YYYY");

  listOfInstrucitons.forEach((instruction) => {
    instruction.lines
      .filter((line) => line.label)
      .forEach((line, index) => {
        calEventArr.push({
          title: line.label,
          display: "auto",
          start: dayjs(instruction["dateTime"], "MM/DD/YYYY").format(
            "YYYY-MM-DD"
          ),
          index: index,
        });

        if (dayjs(instruction["dateTime"], "MM/DD/YYYY").isBefore(minDate)) {
          minDate = dayjs(instruction["dateTime"], "MM/DD/YYYY");
        }
      });
  });

  const handlePrint = () => {
    ReactGA.event({
      category: "Calendar View",
      action: "Print",
      label: "Calendar View Print",
    });

    window.print();
  };

  return (
    <div className="calendar-container">
      <h2>Protocol #{SynchronizationProtocol}</h2>
      <div className="protocol-calendar-wrapper">
        <FullCalendar
          plugins={[dayGridPlugin]}
          initialView="dayGridMonth"
          headerToolbar={{
            left: "prev",
            center: "title",
            right: "next",
          }}
          initialDate={minDate.format("YYYY-MM-DD")}
          eventOrder="index"
          events={calEventArr}
          height={"auto"}
          aspectRatio={1}
        />
      </div>
      <br />
      <center>
        <Button
          variant="contained"
          size="large"
          onClick={() => {
            handlePrint();
          }}
        >
          Print
        </Button>
      </center>
    </div>
  );
};

export default CalendarView;
