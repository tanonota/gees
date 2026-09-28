// ==========================================
// ⚙️ 1. 用途別マトリクス定義（全4カテゴリ）
// ==========================================
const MATRIX_PROPOSAL = {
  "title": "Proposal Viability Index (社内提案・企画成立度)",
  "variables": {
    "L2_BIZ_ROI": { "short_name": "投資対効果の根拠", "description": "リターン（金額・工数削減）の計算ロジックが明確か" },
    "L3_ACT_INV": { "short_name": "現場の自発的投資", "description": "ターゲットがすでに自発的コスト（時間・金）を払っているか" },
    "L5_WTW_MOAT": { "short_name": "社内・競合優位性", "description": "他部署や他社が簡単に模倣できない強みはあるか" },
    "L12_PRD_VAL": { "short_name": "既存比10倍の価値", "description": "「今のやり方」を捨ててまで乗り換える決定打があるか" },
    "L18_MKT_CAC": { "short_name": "導入コストの軽さ", "description": "現場への教育や初期構築の負荷が膨らみすぎていないか" },
    "L22_OPR_KPI": { "short_name": "定量的な撤退ルール", "description": "成功・失敗を定量的に判断する撤退ルールがあるか" },
    "L26_RES_MNY": { "short_name": "スモールスタート性", "description": "最小限の予算と人数で今すぐ検証を開始できるか" }
  }
};

const MATRIX_IMPROVEMENT = {
  "title": "Growth Bottleneck Score (事業改善・定着阻害要因)",
  "variables": {
    "L1_USR_HDL": { "short_name": "物理的な利用障壁", "description": "離脱原因が「意欲不足（精神論）」ではなく手間の壁か" },
    "L6_SWT_CST": { "short_name": "他社への乗り換え壁", "description": "使えば使うほど解約しにくくなる仕組みがあるか" },
    "L11_PRD_UX": { "short_name": "迷わない操作性", "description": "マニュアルなしで直感的に使いこなせる設計か" },
    "L14_PRD_RTN": { "short_name": "自然な再利用フック", "description": "ユーザーが自発的に戻ってくる仕組み（通知等）があるか" },
    "L21_OPR_MVP": { "short_name": "高速改善サイクル", "description": "大改修ではなく小回りの利くA/Bテストができているか" },
    "L24_OPR_CS": { "short_name": "サポートの省力化", "description": "ユーザー対応が人力依存で破綻していないか" }
  }
};

const MATRIX_RESEARCH = {
  "title": "Customer Pain Clarity (顧客課題の解像度)",
  "variables": {
    "L1_USR_HDL": { "short_name": "切実な痛みの強さ", "description": "「あったらいい」ではなく「ないと困る」切実さか" },
    "L3_ACT_INV": { "short_name": "過去の自発的行動", "description": "過去1年以内に自力で課題解決（検索・購入等）を試みたか" },
    "L4_MKT_GAP": { "short_name": "共通課題の市場規模", "description": "同じ痛みを抱えるユーザーが一定数以上存在するか" },
    "L13_PRD_FIT": { "short_name": "現行業務への適合", "description": "顧客の日常業務や生活の流れを邪魔しないか" },
    "L15_PRD_ALT": { "short_name": "代替手段への勝率", "description": "今使っている代替手段（紙・Excel等）に圧倒的に勝てるか" },
    "L25_OPR_TRG": { "short_name": "ターゲットの鮮明さ", "description": "「どんな状況の誰か」が具体的に特定されているか" }
  }
};

const MATRIX_DX_OPS = {
  "title": "DX Adoption & Impact (社内DX定着・インパクト)",
  "variables": {
    "L1_USR_HDL": { "short_name": "入力ストレスゼロ度", "description": "入力項目や手順が現場の物理的負担になっていないか" },
    "L7_ORG_ALN": { "short_name": "自発インセンティブ", "description": "システムを使うと「担当者自身」が得をする構造か" },
    "L11_PRD_UX": { "short_name": "慣れ親しんだ体験", "description": "普段使いのチャットやメール感覚で扱えるか" },
    "L16_PRD_OPS": { "short_name": "実質的な工数削減幅", "description": "ツールの運用手間が削減効果を相殺していないか" },
    "L21_OPR_MVP": { "short_name": "パイロット運用の柔軟性", "description": "1チームで試行錯誤してから全社展開できるか" },
    "L26_RES_MNY": { "short_name": "自立運用・低コスト性", "description": "外部頼みにならず社内リソースで維持できるか" }
  }
};

// ==========================================
// 🛠️ 2. 動的切り替え用ヘルパー関数
// ==========================================
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
// 🧠 3. AI推論エンジン（Gemini API）
// ==========================================
function evaluateRigapFromAppSheet(scriptId, mode, targetAttribute, reality, ideal, gap, action, pain) {
  const ss = SpreadsheetApp.openById("1zPuFqRni7VaGO4wsCzNpS7QxmM_o-iNR2eThyte4wP8");
  const rigapSheet = ss.getSheetByName('RIGAP_Scripts');

  const matrixData = getMatrixByMode(mode);

  const prompt = `
  あなたは生存率3%の罠を突破するための「科学的事業開発」の専門コンサルタントです。
  提供された理論に基づき、ユーザーの入力を分析し、事業の解像度と不整合を評価してください。

  【今回の評価テーマ】
  ${matrixData.title}

  【評価基準（この中から重要項目を5つ選定して採点）】
  ${JSON.stringify(matrixData.variables, null, 2)}

  【ユーザー入力】
  - ターゲット属性: ${targetAttribute}
  - 現状: ${reality}
  - 理想: ${ideal}
  - 乖離: ${gap}
  - アクション: ${action}
  - ペイン: ${pain}

  【タスク】
  以下のJSON形式のスキーマで厳密に出力してください。
  {
    "overall_summary": "アイデア全体の総評と、最も致命的な不整合の指摘（200文字程度）",
    "field_analysis": {
      "target": "ターゲット設定に対する評価",
      "reality": "現状の解像度に対する評価",
      "ideal": "理想設定に対する評価",
      "gap": "乖離の構造化に対する評価",
      "action": "投資事実（アクション）の評価",
      "pain": "障壁が精神論か物理的かの評価"
    },
    "matrix_evaluations": [
      {
        "variable_name": "評価基準の short_name の文字列をそのまま使用",
        "score": 0から100の数値,
        "reason": "なぜその点数なのかの理由"
      }
    ]
  }
  `;

  const apiKey = "*************"; 
  const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=${apiKey}`;
  const payload = { "contents": [{"parts": [{"text": prompt}]}], "generationConfig": { "responseMimeType": "application/json" } };
  const options = { "method": "post", "contentType": "application/json", "payload": JSON.stringify(payload), "muteHttpExceptions": true };

  const response = UrlFetchApp.fetch(url, options);
  const responseText = response.getContentText();
  const json = JSON.parse(responseText);

  try {
    if (json.error) throw new Error(`Gemini API エラー: ${json.error.message}`);
    if (!json.candidates || json.candidates.length === 0) throw new Error("AIからの回答が空でした。");

    const resultText = json.candidates[0].content.parts[0].text;
    const evaluation = JSON.parse(resultText);

    // RIGAP_ScriptsシートのJ列(10)に全体総評を記録
    const rigapData = rigapSheet.getDataRange().getValues();
    for (let i = 1; i < rigapData.length; i++) {
      if (rigapData[i][0] === scriptId) { 
        rigapSheet.getRange(i + 1, 10).setValue(evaluation.overall_summary); 
        break;
      }
    }

    return JSON.stringify(evaluation); 

  } catch (e) {
    Logger.log("【API生レスポンス】: " + responseText);
    throw new Error("APIエラー詳細: " + e.toString() + "\n【生レスポンス】: " + responseText);
  }
}

// ==========================================
// 🌐 4. Webアプリの受付窓口
// ==========================================
function doGet() {
  return HtmlService.createHtmlOutputFromFile('index')
    .setTitle('AI Business Consulting Bot')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL)
    .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

function processFromWeb(formData) {
  try {
    const ss = SpreadsheetApp.openById("**********");
    const sheet = ss.getSheetByName("RIGAP_Scripts");
    
    const newRowId = Utilities.getUuid(); 
    // mode情報もスプレッドシートに記録する（B列に入る想定）
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
    
    return evaluateRigapFromAppSheet(newRowId, formData.mode, formData.targetAttribute, formData.reality, formData.ideal, formData.gap, formData.action, formData.pain);
    
  } catch(e) {
    throw new Error(e.message);
  }
}
