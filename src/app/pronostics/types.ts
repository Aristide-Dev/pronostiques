// types.ts
export interface Prediction {
    source: string;
    prediction: string;
    odds: number;
  }
  
  export interface Match {
    teams: string;
    date: string;
    predictions: Prediction[];
  }
  