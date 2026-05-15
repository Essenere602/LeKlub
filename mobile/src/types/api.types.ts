export type ApiResponse<TData> = {
  success: boolean;
  data: TData | null;
  message: string | null;
  errors: Record<string, string[]> | [];
};

export type ApiError = {
  message: string;
  errors?: Record<string, string[]> | [];
  status?: number;
};
