// ==========================================
// 用途別マトリクス定義
// ==========================================
const MATRIX_PROPOSAL = {
  "title": "Proposal Viability Index (社内提案・企画成立度)",
  "variables": {
    "ROI": { "short_name": "投資対効果の根拠", "description": "リターン（金額・工数削減）の計算ロジックが明確か" },
    "INV": { "short_name": "現場の自発的投資", "description": "ターゲットがすでに自発的コスト（時間・金）を払っているか" },
    "MOAT": { "short_name": "社内・競合優位性", "description": "他部署や他社が簡単に模倣できない強みはあるか" },
    "VAL": { "short_name": "既存比10倍の価値", "description": "「今のやり方」を捨ててまで乗り換える決定打があるか" },
    "CAC": { "short_name": "導入コストの軽さ", "description": "現場への教育や初期構築の負荷が膨らみすぎていないか" },
    "KPI": { "short_name": "定量的な撤退ルール", "description": "成功・失敗を定量的に判断する撤退ルールがあるか" },
    "MNY": { "short_name": "スモールスタート性", "description": "最小限の予算と人数で今すぐ検証を開始できるか" }
  }
};

const MATRIX_IMPROVEMENT = {
  "title": "Growth Bottleneck Score (事業改善・定着阻害要因)",
  "variables": {
    "HDL": { "short_name": "物理的な利用障壁", "description": "離脱原因が「意欲不足（精神論）」ではなく手間の壁か" },
    "CST": { "short_name": "他社への乗り換え壁", "description": "使えば使うほど解約しにくくなる仕組みがあるか" },
    "UX": { "short_name": "迷わない操作性", "description": "マニュアルなしで直感的に使いこなせる設計か" },
    "RTN": { "short_name": "自然な再利用フック", "description": "ユーザーが自発的に戻ってくる仕組み（通知等）があるか" },
    "MVP": { "short_name": "高速改善サイクル", "description": "大改修ではなく小回りの利くA/Bテストができているか" },
    "CS": { "short_name": "サポートの省力化", "description": "ユーザー対応が人力依存で破綻していないか" }
  }
};

const MATRIX_RESEARCH = {
  "title": "Customer Pain Clarity (顧客課題の解像度)",
  "variables": {
    "HDL": { "short_name": "切実な痛みの強さ", "description": "「あったらいい」ではなく「ないと困る」切実さか" },
    "INV": { "short_name": "過去の自発的行動", "description": "過去1年以内に自力で課題解決（検索・購入等）を試みたか" },
    "GAP": { "short_name": "共通課題の市場規模", "description": "同じ痛みを抱えるユーザーが一定数以上存在するか" },
    "FIT": { "short_name": "現行業務への適合", "description": "顧客の日常業務や生活の流れを邪魔しないか" },
    "ALT": { "short_name": "代替手段への勝率", "description": "今使っている代替手段（紙・Excel等）に圧倒的に勝てるか" },
    "TRG": { "short_name": "ターゲットの鮮明さ", "description": "「どんな状況の誰か」が具体的に特定されているか" }
  }
};

const MATRIX_DX_OPS = {
  "title": "DX Adoption & Impact (社内DX定着・インパクト)",
  "variables": {
    "HDL": { "short_name": "入力ストレスゼロ度", "description": "入力項目や手順が現場の物理的負担になっていないか" },
    "ALN": { "short_name": "自発インセンティブ", "description": "システムを使うと「担当者自身」が得をする構造か" },
    "UX": { "short_name": "慣れ親しんだ体験", "description": "普段使いのチャットやメール感覚で扱えるか" },
    "OPS": { "short_name": "実質的な工数削減幅", "description": "ツールの運用手間が削減効果を相殺していないか" },
    "MVP": { "short_name": "パイロット運用の柔軟性", "description": "1チームで試行錯誤してから全社展開できるか" },
    "MNY": { "short_name": "自立運用・低コスト性", "description": "外部頼みにならず社内リソースで維持できるか" }
  }
};
const SPREADSHEET_ID = "*********"; 

function getMatrixByMode(mode) {
  switch (mode) {
    case 'PROPOSAL': return MATRIX_PROPOSAL;
    case 'IMPROVEMENT': return MATRIX_IMPROVEMENT;
    case 'RESEARCH': return MATRIX_RESEARCH;
    case 'DX_OPS': return MATRIX_DX_OPS;
    default: return MATRIX_PROPOSAL;
  }
}

// ==========================================
// AI推論エンジン
// ==========================================
function evaluateRigapFromAppSheet(rowIndex, mode, targetAttribute, reality, ideal, gap, action, pain) {
  const apiKey = PropertiesService.getScriptProperties().getProperty('GEMINI_API_KEY');
  if (!apiKey) {
    throw new Error("APIキーが設定されていません。GASのスクリプトプロパティに 'GEMINI_API_KEY' を登録してください。");
  }

  const currentMatrix = getMatrixByMode(mode);

  const prompt = `
  あなたは生存率3%の罠を突破するための「科学的事業開発」の専門コンサルタントです。
  提供された『科学的事業開発の手引書』の理論に基づき、ユーザーの入力を分析し、事業の解像度と不整合を深掘りして論理的かつ具体的に評価してください。

  【手引書の定義（評価基準）】
  ${JSON.stringify(currentMatrix, null, 2)}

  【ユーザー入力（RIGAP＋属性）】
  - ターゲット属性: ${targetAttribute}
  - 現状: ${reality}
  - 理想: ${ideal}
  - 乖離: ${gap}
  - アクション: ${action}
  - ペイン: ${pain}

  【タスク】
  以下のJSON形式のスキーマで厳密に出力してください。
  {
    "overall_summary": "事業アイデア全体の総評と、最も致命的な不整合（ボトルネック）の指摘（200文字〜250文字程度で論理的かつ鋭く解説してください）",
    "field_analysis": {
      "target": "ターゲット設定に対する冷徹な評価と課題（具体的に解説）",
      "reality": "現状の解像度に対する評価（具体的に解説）",
      "ideal": "理想設定に対する評価（具体的に解説）",
      "gap": "乖離の構造化に対する評価（具体的に解説）",
      "action": "投資事実（アクション）の具体性と有効性への評価（具体的に解説）",
      "pain": "障壁が精神論か物理的かの評価（具体的に解説）"
    },
    "matrix_evaluations": [
      {
        "variable_name": "選定した変数の short_name（例：投資対効果の根拠）",
        "score": 0から100の数値,
        "reason": "なぜその点数なのかの理由（具体的に解説）"
      }
    ]
  }
  ※matrix_evaluationsは、入力内容から推測して最も重要となる変数を【5つ】選定して採点してください。
  ※variable_nameには変数キーなどの英数字を含めず、必ず「short_name」の文字列のみを出力してください。
  `;

  const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=${apiKey}`;
  const payload = { 
    "contents": [{"parts": [{"text": prompt}]}], 
    "generationConfig": { 
      "responseMimeType": "application/json",
      "maxOutputTokens": 8192 
    } 
  };
  const options = { 
    "method": "post", 
    "contentType": "application/json", 
    "payload": JSON.stringify(payload), 
    "muteHttpExceptions": true 
  };

  let apiResponse = null;
  const maxRetries = 3;
  let delay = 2000;

  for (let i = 0; i < maxRetries; i++) {
    try {
      apiResponse = UrlFetchApp.fetch(url, options);
    } catch (e) {
      throw new Error("APIとの通信に失敗しました: " + e.message);
    }

    const responseCode = apiResponse.getResponseCode();

    if (responseCode === 200) {
      break; 
    } else if (responseCode === 503) {
      if (i < maxRetries - 1) {
        Utilities.sleep(delay);
        delay += 2000;
      } else {
        throw new Error("現在AIサーバーが非常に混雑しています。しばらく時間を置いてから再度お試しください。");
      }
    } else if (responseCode === 429) {
      throw new Error("リクエスト上限に達しました。少し時間を置いてから再度お試しください。");
    } else {
      throw new Error(`APIエラー (HTTP ${responseCode}): ${apiResponse.getContentText()}`);
    }
  }

  try {
    let resultText = JSON.parse(apiResponse.getContentText()).candidates[0].content.parts[0].text;
    resultText = resultText.replace(/^```json\s*/, '').replace(/\s*```$/, '').trim();
    const evaluation = JSON.parse(resultText);

    if (rowIndex) {
      const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
      const rigapSheet = ss.getSheetByName('RIGAP_Scripts');
      if (rigapSheet) {
        rigapSheet.getRange(rowIndex, 10).setValue(evaluation.overall_summary);
      }
    }

    return JSON.stringify(evaluation); 

  } catch (e) {
    throw new Error("データ解析に失敗しました: " + e.message);
  }
}

function doGet() {
  return HtmlService.createHtmlOutputFromFile('index')
    .setTitle('AI Business Consulting Bot')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL)
    .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

function processFromWeb(formData) {
  const lock = LockService.getScriptLock();
  try {
    lock.waitLock(10000);
    
    const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    const sheet = ss.getSheetByName("RIGAP_Scripts");
    
    const newRowId = Utilities.getUuid(); 
    sheet.appendRow([
      newRowId,
      formData.mode,
      formData.targetAttribute,
      formData.reality,
      formData.ideal,
      formData.gap,
      formData.action,
      formData.pain
    ]);
    
    const newRowIndex = sheet.getLastRow();
    lock.releaseLock();
    
    return evaluateRigapFromAppSheet(
      newRowIndex, 
      formData.mode, 
      formData.targetAttribute, 
      formData.reality, 
      formData.ideal, 
      formData.gap, 
      formData.action, 
      formData.pain
    );
    
  } catch(e) {
    if (lock.hasLock()) lock.releaseLock();
    throw new Error(e.message);
  }
}
