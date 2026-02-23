import { React, useEffect, useMemo } from "react";
import { Button, ButtonGroup } from "@mui/material";
import { getProtocolsData, getRulesData } from '../utils/dataLoader';

import "../style/validProtocols.css";

const ProtocolData = getProtocolsData();
const engineRules = getRulesData();

const ValidProtocols = (props) => {
  const {
    UserFlow,
    setUserFlow,
    BreedType,
    CowOrHeifer,
    SemenType,
    SystemType,
    setSynchronizationProtocol,
    preferList,
    setPreferList,
    lessPreferList,
    setLessPreferList,
  } = props;

  // create a rules engine
  const { Engine } = require("json-rules-engine");
  var SynchProtocolTitleData = ProtocolData.Protocols[0];

  // Get System Type header dynamically from ParametersMeta
  const paramsMeta = ProtocolData.ParametersMeta || {};
  const systemTypeMeta = paramsMeta['System Type'] || {};

  // Use header from meta, or fall back to the SystemType value itself
  let selectedProtocolHeader = systemTypeMeta[SystemType]?.header || SystemType || "";

  const factInput = useMemo(() => ({
    BreedType: BreedType,
    SemenType: SemenType,
    SystemType: SystemType,
  }), [BreedType, SemenType, SystemType]);

  // this is for the prefer systems
  useEffect(() => {
    const fetchData1 = async () => {
      let protocolsArr = [];
      const preferredKey = `${CowOrHeifer} Preferred Systems`;

      // Check if rules exist for this animal type
      if (!engineRules[preferredKey]) {
        setPreferList([]);
        return;
      }

      for (
        let i = 1;
        i <= Object.keys(engineRules[preferredKey]).length;
        i++
      ) {
        let engine = new Engine();
        engineRules[preferredKey][i].forEach((item) => {
          engine.addRule(item);
        });

        const data = await engine.run(factInput);

        data.events.forEach((item) => {
          protocolsArr.push(item.type);
        });
      }
      setPreferList(protocolsArr);
    };

    fetchData1().catch(console.error);
  }, [CowOrHeifer, Engine, engineRules, factInput, setPreferList]);

  // this is for the less preferr systems
  useEffect(() => {
    const fetchData1 = async () => {
      let protocolsArr = [];
      const lessPreferredKey = `${CowOrHeifer} Less Preferred Systems`;

      // Check if rules exist for this animal type
      if (!engineRules[lessPreferredKey]) {
        setLessPreferList([]);
        return;
      }

      for (
        let i = 1;
        i <= Object.keys(engineRules[lessPreferredKey]).length;
        i++
      ) {
        let engine = new Engine();
        engineRules[lessPreferredKey][i].forEach((item) => {
          engine.addRule(item);
        });
        const data = await engine.run(factInput);

        data.events.forEach((item) => {
          protocolsArr.push(item.type);
        });
      }
      setLessPreferList(protocolsArr);
    };

    fetchData1().catch(console.error);
  }, [CowOrHeifer, Engine, engineRules, factInput, setLessPreferList]);

  return (
    <div className="protocol-div-container">
      <br />
      <br />
      <div>
        <h2>
          {" "}
          {selectedProtocolHeader} - {CowOrHeifer} Protocols{" "}
        </h2>
        <ButtonGroup
          orientation="vertical"
          aria-label="vertical contained button group"
          variant="text"
        >
          {preferList.map((item, index) => {
            if (item !== "") {
              return (
                <Button
                  key={index}
                  onClick={() => {
                    setSynchronizationProtocol(item);
                    setUserFlow(UserFlow + 1);
                  }}
                >
                  ({item}){" "}
                  {SynchProtocolTitleData[item].SynchronizationSystemTitle}
                </Button>
              );
            } else {
              return null;
            }
          })}
        </ButtonGroup>

        <h2> Other Systems </h2>
        <ButtonGroup
          orientation="vertical"
          aria-label="vertical contained button group"
          variant="text"
        >
          {lessPreferList.map((item, index) => {
            if (item !== "") {
              return (
                <Button
                  key={index}
                  onClick={() => {
                    setSynchronizationProtocol(item);
                    setUserFlow(UserFlow + 1);
                  }}
                >
                  ({item}){" "}
                  {SynchProtocolTitleData[item].SynchronizationSystemTitle}
                </Button>
              );
            } else {
              return null;
            }
          })}
        </ButtonGroup>
      </div>
      <br />
      <Button
        onClick={() => {
          setUserFlow(UserFlow - 1);
        }}
        variant="outlined"
        size="large"
        className="custom-btn-styling-stuff"
      >
        Return
      </Button>
    </div>
  );
};

export default ValidProtocols;
