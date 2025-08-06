import React, { useState } from "react";
import { Button, Breadcrumbs, Link, Typography, Grid } from "@mui/material";
import ListView from "./ListView";
import CalendarView from "./CalendarView";
import PInstructions from "../Protocols.json";
import swal from "sweetalert";
import dayjs from "dayjs";

const ics = require("ics");
var FileSaver = require("file-saver");

const ProtocolInstructions = (props) => {
  const {
    UserFlow,
    setUserFlow,
    DateToStartBreeding,
    SynchronizationProtocol,
    GNRH,
    PG,
    BullTurnIn,
    GestationPeriod,
    SemenType,
  } = props;

  //Variable to determine what page we are on
  const [CalendarOrListView, setCalendarOrListView] = useState(0);
  let ListOfInstrucitons =
    PInstructions.Protocols[0][SynchronizationProtocol].instructions;

  let selectedGNRH;
  let selectedPG;
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

  //store if breed females AI 16-22....
  // G14 -> Semen Type
  if (SemenType === "Conventional & Sexed") {
    ai_standing_heat_txt = "Breed females AI 16-22 hours after standing heat.";
    ai_with_sexed_semen = "AI with sexed semen estrous females";
    ai_with_sexed_semen_showing =
      "AI with sexed semen those showing females estrus";
    ai_with_sexed_semen_plus_conventional =
      "AI with sexed semen estrous females.  All others with conventional semen.";
    nonestrous_females = "Inject 2cc Cystorelin (GnRH) to nonestrous females.";
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
    nonestrous_females = "Inject 2cc Cystorelin (GnRH) to all females.";
    cidr_device = "Remove the CIDR device from each female.";
  }

  switch (true) {
    case GNRH === "Cystorelin":
      selectedGNRH = "2cc Cystorelin (GnRH)";
      break;
    case GNRH === "Factrel":
      selectedGNRH = "2cc Factrel (GnRH)";
      break;
    case GNRH === "Fertagyl":
      selectedGNRH = "2cc Fertagyl (GnRH)";
      break;
    case GNRH === "OvaCyst":
      selectedGNRH = "2cc OvaCyst (GnRH)";
      break;
    case GNRH === "GONAbreed":
      selectedGNRH = "1cc GONAbreed (GnRH)";
      break;
    case GNRH === "GnRH":
      selectedGNRH = "(GnRH)";
      break;
    default:
      break;
  }

  switch (true) {
    case PG === "Estrumate":
      selectedPG = "2cc Estrumate (PG)";
      break;
    case PG === "EstroPLAN":
      selectedPG = "2cc EstroPLAN (PG)";
      break;
    case PG === "InSynch":
      selectedPG = "5cc InSynch (PG)";
      break;
    case PG === "Lutalyse":
      selectedPG = "5cc Lutalyse (PG)";
      break;
    case PG === "ProstaMate":
      selectedPG = "5cc ProstaMate (PG)";
      break;
    case PG === "HiConc.Lutalyse":
      selectedPG = "2cc HiConc.Lut. (PG)";
      break;
    case PG === "Synchsure":
      selectedPG = "2cc Synchsure (PG)";
      break;
    case PG === "PG":
      selectedPG = "(PG)";
      break;
    default:
      break;
  }

  listOfInstrucitons.forEach((instruction, key) => {
    const pgInstruction = listOfInstrucitons.find((item) => item.isPg);
    const pgDate = dateToStartBreeding
      .clone()
      .add(pgInstruction.dateAdjustment, pgInstruction.dateAdjustmentUnit);
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
    instruction["dateStr"] = mainDate.toISOString();
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

  //Create then download the iCalendar file
  const downloadICS = () => {
    let listOfEvents = [];

    listOfInstrucitons.forEach((instruction) => {
      let description = instruction.lines.map((line) => line.label).join("\n");
      console.log(instruction);
      let eventDictionary = {};
      eventDictionary["title"] = description || "";
      eventDictionary["description"] = description;

      const eventDate = dayjs(instruction.dateTime, "MM/DD/YYYY HH:mm");

      eventDictionary["start"] = [
        eventDate.year(),
        eventDate.month() + 1,
        eventDate.date(),
        eventDate.hour(),
        eventDate.minute(),
      ];
      eventDictionary["duration"] = { hours: 1 };
      listOfEvents.push(eventDictionary);
    });

    console.log(listOfEvents);

    const { error, value } = ics.createEvents(listOfEvents);

    if (error) {
      swal(error);
      console.log(error);
      return;
    }
    //create file then download
    var file = new File([value], "EstrusScheduleDownload.ics", {
      type: "text/plain;charset=utf-8",
    });
    FileSaver.saveAs(file);
  };

  return (
    <>
      <br />
      <Breadcrumbs className="bread-crumb-class">
        <Link
          onClick={() => {
            setUserFlow(UserFlow - 2);
          }}
        >
          Home
        </Link>
        <Link
          onClick={() => {
            setUserFlow(UserFlow - 1);
          }}
        >
          Protocol
        </Link>
        <Typography aria-label="breadcrumb">Instructions</Typography>
      </Breadcrumbs>
      <br />
      <div className="instruction-container">
        <Grid container justifyContent="flex-end" className="calendar-btns">
          <Button
            size="large"
            onClick={() => {
              if (CalendarOrListView) {
                setCalendarOrListView(0);
              } else {
                setCalendarOrListView(1);
              }
            }}
          >
            View {CalendarOrListView ? "List" : "Calendar"}
          </Button>
          <Button
            size="large"
            onClick={() => {
              downloadICS();
            }}
          >
            iCalendar file (.ics)
          </Button>
        </Grid>
      </div>

      {/* Show the Calendar view or the List View ternary */}
      {CalendarOrListView === 0 ? (
        <ListView
          UserFlow={UserFlow}
          setUserFlow={setUserFlow}
          ListOfInstrucitons={ListOfInstrucitons}
          DateToStartBreeding={DateToStartBreeding}
          SynchronizationProtocol={SynchronizationProtocol}
          GNRH={GNRH}
          PG={PG}
          BullTurnIn={BullTurnIn}
          GestationPeriod={GestationPeriod}
          SemenType={SemenType}
        />
      ) : (
        <CalendarView
          ListOfInstrucitons={ListOfInstrucitons}
          DateToStartBreeding={DateToStartBreeding}
          SynchronizationProtocol={SynchronizationProtocol}
          GNRH={GNRH}
          PG={PG}
          BullTurnIn={BullTurnIn}
          GestationPeriod={GestationPeriod}
          SemenType={SemenType}
        />
      )}
    </>
  );
};

export default ProtocolInstructions;
